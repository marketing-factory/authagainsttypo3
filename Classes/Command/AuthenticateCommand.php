<?php
namespace Mfc\AuthAgainstTypo3\Command;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Saltedpasswords\Salt\SaltFactory;
use TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Class AuthenticateCommand
 */
class AuthenticateCommand extends \TYPO3\CMS\Core\Controller\CommandLineController
{
    /**
     * @var string
     */
    public $extkey = 'authagainsttypo3';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Setting help texts:
        $this->cli_help['name'] = 'cliAuthBE';
        $this->cli_help['synopsis'] = 'authENV, authPARAM';
        $this->cli_help['description'] = 'Auth CLI Script for using mod-auth-external. ' .
            'USER and PASS must be set via Environment';
        $this->cli_help['examples'] = '/.../cli_dispatch.phpsh authagainsttypo3';
        $this->cli_help['author'] = 'Ingo Schmitt, (c) 2012';
    }

    /**
     * CLI engine
     *
     * @return string
     */
    public function cliMain()
    {
        // get task (function)
        $task = (string)$this->cli_args['_DEFAULT'][1];

        switch ($task) {
            case 'authENV':
                $credentials = $this->getEnv();
                $this->authAgainstBe($credentials);
                break;

            case 'authPARAM':
                $credentials = $this->getCliArgs();
                $this->authAgainstBe($credentials);
                break;

            default:
                $this->cli_validateArgs();
                $this->cli_help();
                exit;
        }
    }

    /**
     * @param $credentials
     * @return void
     */
    public function authAgainstBe($credentials)
    {
        $password = $credentials['pass'];

        $user = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
            '*',
            'be_users',
            'username = ' . $this->getDatabaseConnection()->fullQuoteStr(
                $credentials['user'],
                'be_users'
            ) . ' AND disable = 0 AND deleted = 0'
        );

        if (empty($user)) {
            $error = 'Invalid user';
            $validPassword = 0;
        } else {
            $error = 'Invalid password';
            if (ExtensionManagementUtility::isLoaded('saltedpasswords') &&
                SaltedPasswordsUtility::isUsageEnabled('BE')
            ) {
                $saltObject = null;
                if (strpos($user['password'], '$1') === 0) {
                    $saltObject = SaltFactory::setPreferredHashingMethod('tx_saltedpasswords_salts_md5');
                }
                if (!is_object($saltObject)) {
                    $saltObject = SaltFactory::getSaltingInstance($user['password']);
                }
                if (is_object($saltObject)) {
                    $validPassword = $saltObject->checkPassword($password, $user['password']);
                } else {
                    if (GeneralUtility::inList('C$,M$', substr($user['password'], 0, 2))) {
                        // Instantiate default method class
                        $saltObject = SaltFactory::getSaltingInstance(
                            substr(
                                $user['password'],
                                1
                            )
                        );
                        // md5
                        if ($user['password'][0] === 'M') {
                            $validPassword = $saltObject->checkPassword(md5($password), substr($user['password'], 1));
                        } else {
                            $validPassword = $saltObject->checkPassword($password, substr($user['password'], 1));
                        }
                    } else {
                        $validPassword = 0;
                    }
                }
            } else {
                $validPassword = md5($password) == $user['password'];
            }
        }

        if (!$validPassword) {
            print $error;
        } else {
            print 'Valid login';
        }

        exit ((int)!$validPassword);
    }

    /**
     * Get environment values
     *
     * @return array
     */
    public function getEnv()
    {
        $user = trim(getenv('USER'));
        $pass = trim(getenv('PASS'));

        return array('user' => $user, 'pass' => $pass);
    }

    /**
     * Get console arguments
     *
     * @return array
     */
    public function getCliArgs()
    {
        $user = trim($this->cli_args['_DEFAULT'][2]);
        $pass = trim($this->cli_args['_DEFAULT'][3]);

        return array('user' => $user, 'pass' => $pass);
    }


    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}

/** @var \Mfc\AuthAgainstTypo3\Command\AuthenticateCommand $command */
$command = GeneralUtility::makeInstance('Mfc\\AuthAgainstTypo3\\Command\\AuthenticateCommand');
$command->cliMain();
