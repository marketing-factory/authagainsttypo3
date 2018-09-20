<?php
namespace Mfc\Authagainsttypo3\Authentication;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Saltedpasswords\Salt\SaltFactory;

/**
 * Class ConsoleAuthentication
 */
class ConsoleAuthentication extends \TYPO3\CMS\Core\Controller\CommandLineController
{
    /**
     * @var array
     */
    public $cli_options = array(
        array('authENV', 'login user with credentials taken from environment'),
        array('authPARAM', 'login user with credentials taken from commmandline arguments'),
    );

    /**
     * @var array
     */
    public $cli_help = array(
        'name' => 'cliAuthBE',
        'synopsis' => '###OPTIONS###',
        'description' => 'Auth CLI Script for using mod-auth-external.
USER and PASS must be set via environement or commandline arguments.',
        'examples' => '/.../cli_dispatch.phpsh authagainsttypo3 OPTION',
        'options' => '',
        'license' => 'GNU GPL - free software!',
        'author' => 'Ingo Schmitt, (c) 2012'
    );

    /**
     * Login user either with environment or commandline arguments
     *
     * @param array $argv Command line arguments
     */
    public function main($argv)
    {
        switch ($argv[1]) {
            case 'authENV':
               	$this->authENV();
                break;

            case 'authPARAM':
                $this->authPARAM($argv);
                break;

            default:
                $this->cli_validateArgs();
                $this->cli_help();
                exit;
        }
    }

    public function authENV() {
		$this->authenticateAgainstBackendUsers($this->getCredentialsFromEnvironment());
	}

    public function authPARAM($arguments) {
		$this->authenticateAgainstBackendUsers($this->getCredentialsFromCommandlineArguments($arguments));
	}

    /**
     * @param $loginData
     * @return void
     */
    public function authenticateAgainstBackendUsers($loginData)
    {
        $loginData['status'] = 'login';
        $userData = $this->getBackendUserByUsername($loginData['uname']);

        $authenticated = false;
        if (empty($userData)) {
            $error = 'Invalid user';
        } else {
            $error = 'Invalid password';
            $this->removeProblematicServices();
            $this->updatePasswordWithCurrentSaltingMethod($userData);

            $authenticated = $this->authenticateWithAuthServices($userData, $loginData);
            $authenticated = $this->authenticateWithMd5($userData, $loginData, $authenticated);
        }

        if (!$authenticated) {
            print $error;
        } else {
            print 'Valid login';
        }
        exit ((int)!$authenticated);
    }

    /**
     * @param array $userData
     *
     * @return void
     */
    protected function updatePasswordWithCurrentSaltingMethod(&$userData)
    {
        if (!$this->passwordIsPlaintext($userData['password'])) {
            return;
        }

        $saltFactory = SaltFactory::getSaltingInstance(null, 'BE');
        $userData['password'] = $saltFactory->getHashedPassword($userData['password']);

        $this->getDatabaseConnection()->exec_UPDATEquery(
            'be_users',
            'disable = 0 AND deleted = 0 AND username = ' . $this->getDatabaseConnection()->fullQuoteStr(
                $userData['username'],
                'be_users'
            ),
            array('password' => $userData['password'])
        );
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    protected function passwordIsPlaintext($password)
    {
        return !SaltFactory::determineSaltingHashingMethod($password) && !$this->isValidMd5($password);
    }

    /**
     * @param string $md5
     *
     * @return int
     */
    protected function isValidMd5($md5 = '')
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }


    /**
     * @return array
     */
    protected function getCredentialsFromEnvironment()
    {
        $user = trim(getenv('USER'));
        $pass = trim(getenv('PASS'));

        return array('uname' => $user, 'uident_text' => $pass);
    }

    /**
     * @param array $argv
     * @return array
     */
    protected function getCredentialsFromCommandlineArguments($argv)
    {
        $user = trim($argv[2]);
        $pass = trim($argv[3]);

        return array('uname' => $user, 'uident_text' => $pass);
    }


    /**
     * Returns an info array which provides additional information for auth services
     *
     * @return array
     */
    protected function getAuthInfoArray()
    {
        $authInfo = array();
        $authInfo['loginType'] = 'login';
        $authInfo['refInfo'] = parse_url(GeneralUtility::getIndpEnv('HTTP_REFERER'));
        $authInfo['HTTP_HOST'] = GeneralUtility::getIndpEnv('HTTP_HOST');
        $authInfo['REMOTE_ADDR'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        $authInfo['REMOTE_HOST'] = GeneralUtility::getIndpEnv('REMOTE_HOST');
        return $authInfo;
    }

    /**
     * Remove services that are known to not work with command line authentication like
     * for example captcha protection as we cant display them
     *
     * @return void
     */
    protected function removeProblematicServices()
    {
        $auth =& $GLOBALS['T3_SERVICES']['auth'];

        unset($auth['tx_beuseriprange_sv1']);
        unset($auth['TYPO3\\CMS\\Rsaauth\\RsaAuthService']);
        unset($auth['Mfc\\MfcBeloginCaptcha\\Service\\CaptchaService']);
    }

    /**
     * @param array $userData
     * @param array $loginData
     *
     * @return bool
     */
    protected function authenticateWithAuthServices($userData, $loginData)
    {
        $authInfo = $this->getAuthInfoArray();
        $authenticated = false;

        $serviceChain = '';
        $subType = 'authUserBE';
        while (is_object($serviceObj = GeneralUtility::makeInstanceService('auth', $subType, $serviceChain))) {
            $serviceChain .= ',' . $serviceObj->getServiceKey();
            $serviceObj->initAuth($subType, $loginData, $authInfo, $this);
            if (($ret = $serviceObj->authUser($userData)) > 0) {
                // If the service returns >=200 then no more checking is needed
                // - useful for IP checking without password
                if ((int)$ret >= 200) {
                    $authenticated = true;
                    break;
                } elseif ((int)$ret >= 100) {
                } else {
                    $authenticated = true;
                }
            } else {
                $authenticated = false;
                break;
            }
            unset($serviceObj);
        }

        return $authenticated;
    }

    /**
     * @param array $userData
     * @param array $loginData
     * @param bool $authenticated
     *
     * @return bool
     */
    protected function authenticateWithMd5($userData, $loginData, $authenticated)
    {
        if (!$authenticated && $this->isValidMd5($userData['password'])) {
            $authenticated = md5($loginData['pass']) == $userData['password'];
        }

        return $authenticated;
    }


    /**
     * @param string $username
     *
     * @return array
     */
    protected function getBackendUserByUsername($username)
    {
        return $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
            '*',
            'be_users',
            'disable = 0 AND deleted = 0 AND username = ' . $this->getDatabaseConnection()->fullQuoteStr(
                $username,
                'be_users'
            )
        );
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
