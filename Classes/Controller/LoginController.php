<?php
namespace Mfc\Authagainsttypo3\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Ingo Schmitt <is@marketing-factory.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Plugin 'Auth Against TYPO3' for the 'authagainsttypo3' extension.
 */
class LoginController extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin
{
    /**
     * Same as class name
     *
     * @var string
     */
    public $prefixId = 'tx_authagainsttypo3_pi1';

    /**
     * Path to this script relative to the extension dir.
     *
     * @var string
     */
    public $scriptRelPath = 'pi1/class.tx_authagainsttypo3_pi1.php';

    /**
     * The extension key.
     *
     * @var string
     */
    public $extKey = 'authagainsttypo3';

    /**
     * @var bool
     */
    public $pi_checkCHash = true;

    /**
     * @var string
     */
    protected $version = '0.0.0';

    /**
     * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    protected $lok_fe_user;

    /**
     * @var \DOMDocument
     */
    protected $output;

    /**
     * @var \DOMElement
     */
    protected $root;

    /**
     * The main method of the PlugIn
     *
     * @param string $content The PlugIn content
     * @param array $conf The PlugIn configuration
     *
     * @return string The content that is displayed on the website
     */
    public function main($content, $conf)
    {
        $this->initialize($conf);
        $this->createXmlRoot();

        /**
         * Access-Check. Set IP, User and passwort in TS Constants
         */
        if (GeneralUtility::cmpIP($_SERVER['REMOTE_ADDR'], $this->conf['remoteIp'])
            && GeneralUtility::_POST('serviceUser') == $this->conf['serviceUser']
            && GeneralUtility::_POST('servicePass') == $this->conf['servicePass']
        ) {
            $this->loginFrontendUser();
            $frontendUser = $this->lok_fe_user->user;
            $groupAuth = $this->getGroupInformation();

            if (is_array($frontendUser)) {
                if ($this->conf['fields']) {
                    $fields = GeneralUtility::trimExplode(',', $this->conf['fields']);
                } else {
                    $fields = array_keys($frontendUser);
                }

                $user = array();
                foreach ($fields as $oneField) {
                    $user[trim($oneField)] = $frontendUser[trim($oneField)];
                }
                $userData = $this->output->createElement('fe_user', GeneralUtility::array2xml($user, '', 0, 'fe_user'));
                $this->root->appendChild($userData);
            } else {
                $this->addError(
                    1000,
                    'The given TYPO3 Username and Passwort did not match for the configured Storage PID'
                );
            }
        } else {
            $this->addError(
                9000,
                'You are not allowed to use this service'
            );
        }

        return $this->output->saveXML();
    }

    /**
     * @return void
     */
    protected function createXmlRoot()
    {
        $this->output = new \DOMDocument('1.0', $this->getTypoScriptFrontendController()->renderCharset);
        $this->root = $this->output->createElement('typo3');
        $this->output->appendChild($this->root);

        $this->appendVersion();
    }

    /**
     * In a later Version, we could integrate a Version Check for the Auth Service,
     * when a new Version will be released with an new XML Structure
     *
     * @return void
     */
    protected function appendVersion()
    {
        $version = $this->output->createElement('version');
        $version->setAttribute('nr', $this->version);
        $this->root->appendChild($version);
    }

    /**
     * @param int $id
     * @param string $text
     * @return void
     */
    protected function addError($id, $text)
    {
        $error = $this->output->createElement('error', $text);
        $error->setAttribute('id', $id);
        $this->root->appendChild($error);
    }

    /**
     * @return void
     */
    protected function loginFrontendUser()
    {
        /**
         * Initiate local FE_user for auth
         */
        $this->lok_fe_user = GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication'
        );

        $this->lok_fe_user->lockIP = $this->TYPO3_CONF_VARS['FE']['lockIP'];
        $this->lok_fe_user->lockHashKeyWords = $this->TYPO3_CONF_VARS['FE']['lockHashKeyWords'];
        $this->lok_fe_user->checkPid = $this->TYPO3_CONF_VARS['FE']['checkFeUserPid'];
        $this->lok_fe_user->lifetime = intval($this->TYPO3_CONF_VARS['FE']['lifetime']);
        $this->lok_fe_user->dontSetCookie = 1;
        // List of pid's acceptable
        $this->lok_fe_user->checkPid_value = $this->getDatabaseConnection()->cleanIntList($this->conf['pid']);


        $this->lok_fe_user->start();
        $this->lok_fe_user->unpack_uc('');
        // Gets session data
        $this->lok_fe_user->fetchSessionData();
        $recs = GeneralUtility::_GP('recs');
        if (is_array($recs)) {
            // If any record registration is submitted, register the record.
            $this->lok_fe_user->record_registration($recs, $this->TYPO3_CONF_VARS['FE']['maxSessionDataSize']);
        }
        $this->lok_fe_user->fetchGroupData();
    }

    /**
     * @return bool
     */
    protected function getGroupInformation()
    {
        /**
         * calculate Groups
         */
        $gr_list = '0,-2';
        if (is_array($this->lok_fe_user->user) && count($this->lok_fe_user->groupData['uid'])) {
            $gr_array = $this->lok_fe_user->groupData['uid'];
            // Make unique...
            $gr_array = array_unique($gr_array);
            if (count($gr_array)) {
                // sort
                sort($gr_array);
                $gr_list .= ',' . implode(',', $gr_array);
            }
        }

        $groupAuth = false;
        if (!$this->conf['fe_groups']) {
            foreach (GeneralUtility::trimExplode(',', $this->conf['fe_groups']) as $oneGroup) {
                if (GeneralUtility::inList($this->lok_fe_user->user['usergroup'], $oneGroup)) {
                    $groupAuth = true;
                }
            }
        } else {
            $groupAuth = true;
        }

        return $groupAuth;
    }

    /**
     * @param array $conf
     */
    protected function initialize($conf)
    {
        $this->conf = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();

        $this->includeVersionInformation();
    }

    /**
     * @return void
     */
    protected function includeVersionInformation()
    {
        // Read Version information by loading ext_emconf.php
        $emConfFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('authagainsttypo3', 'ext_emconf.php');
        require_once($emConfFile);

        $this->version = $EM_CONF['authagainsttypo3']['version'];
        if (empty($this->version)) {
            $this->version = '0.0.0';
        }
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
