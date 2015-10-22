<?php
namespace Mfc\AuthAgainstTypo3\Controller;

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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Plugin 'Auth Against TYPO3' for the 'authagainsttypo3' extension.
 *
 * @author    Ingo Schmitt <is@marketing-factory.de>
 * @package    TYPO3
 * @subpackage    tx_authagainsttypo3
 */
class AuthenticateController extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin
{
    /**
     * The extension key.
     *
     * @var string
     */
    public $extKey = 'authagainsttypo3';

    /**
     * @var array
     */
    protected $TYPO3_CONF_VARS;

    /**
     * @var string
     */
    protected $version;

    /**
     * The main method of the PlugIn
     *
     * @param string $_ The PlugIn content
     * @param array $conf The PlugIn configuration
     *
     * @return string The content that is displayed on the website
     */
    public function main($_, $conf)
    {
        $this->init($conf);

        /**
         * build XML by hand is faster than using XML render methods

         */
        $xml = array();
        $xml[] = '<?xml version="1.0" encoding="' . $GLOBALS['TSFE']->renderCharset . '"?>';
        $xml[] = '<typo3>';
        $xml[] = '<version nr="' . $this->version . '"/>';

        /**
         * Access-Check. Set IP, User and password in TS Constants
         */
        if (GeneralUtility::cmpIP($_SERVER['REMOTE_ADDR'], $this->conf['remoteIp'])
            && (GeneralUtility::_POST('serviceUser') == $this->conf['serviceUser'])
            && (GeneralUtility::_POST('servicePass') == $this->conf['servicePass'])
        ) {
            $frontendUser = $this->initFrontendUser();
            $userData = $frontendUser->user;
            $groupList = $this->getGroupList($frontendUser);
            $groupAuth = $this->isGroupAllowed($groupList);

            if (is_array($userData) && $groupAuth) {
                if ($this->conf['fields']) {
                    $fields = GeneralUtility::trimExplode(',', $this->conf['fields'], true);
                } else {
                    $fields = array_keys($userData);
                }

                $user = array();
                foreach ($fields as $field) {
                    $user[$field] = $userData[$field];
                }
                $xml[] = GeneralUtility::array2xml($user, '', 0, 'fe_user');
            } else {
                $xml[] = '<error id="1000">The given TYPO3 Username and Password did
not match for the configured Storage PID</error>';
            }
        } else {
            $xml[] = '<error id="9000">You are not allowed to use this service</error>';
        }
        $xml[] = '</typo3>';

        return implode(LF, $xml);
    }

    /**
     * Initialize
     *
     * @param array $conf
     */
    protected function init($conf)
    {
        $this->TYPO3_CONF_VARS = $GLOBALS['TYPO3_CONF_VARS'];
        $this->conf = $conf;

        // Read Version information by loading ext_emconf.php
        $_EXTKEY = $this->extKey;
        $a = require_once(ExtensionManagementUtility::extPath('authagainsttypo3') . 'ext_emconf.php');

        $this->version = $EM_CONF['authagainsttypo3']['version'];
        if (empty($this->version)) {
            $this->version = '0.0.0';
        }
    }

    /**
     * Instantiate local FrontendUserAuthentication for auth
     *
     * @return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser
     */
    protected function initFrontendUser()
    {
        $frontendUser = GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication'
        );

        $frontendUser->lockIP = $this->TYPO3_CONF_VARS['FE']['lockIP'];
        $frontendUser->lockHashKeyWords = $this->TYPO3_CONF_VARS['FE']['lockHashKeyWords'];
        $frontendUser->checkPid = $this->TYPO3_CONF_VARS['FE']['checkFeUserPid'];
        $frontendUser->lifetime = intval($this->TYPO3_CONF_VARS['FE']['lifetime']);
        // List of pid's acceptable
        $frontendUser->checkPid_value = $this->getDatabaseConnection()->cleanIntList($this->conf['pid']);
        $frontendUser->dontSetCookie = 1;

        $frontendUser->start();
        $frontendUser->unpack_uc('');
        // Gets session data
        $frontendUser->fetchSessionData();

        $recs = GeneralUtility::_GP('recs');
        if (is_array($recs)) {
            // If any record registration is submitted, register the record.
            $frontendUser->record_registration(
                $recs,
                $this->TYPO3_CONF_VARS['FE']['maxSessionDataSize']
            );
        }

        $frontendUser->fetchGroupData();

        return $frontendUser;
    }

    /**
     * Calculate Groups
     *
     * @param \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser
     *
     * @return string
     */
    protected function getGroupList($frontendUser)
    {
        $groupList = '0,-2';

        if (is_array($frontendUser->user) && count($frontendUser->groupData['uid'])) {
            $gr_array = $frontendUser->groupData['uid'];
            $gr_array = array_unique($gr_array);
            sort($gr_array);

            if (count($gr_array)) {
                $groupList .= ',' . implode(',', $gr_array);
            }
        }

        return $groupList;
    }

    /**
     * @param string $groupList
     *
     * @return bool
     */
    protected function isGroupAllowed($groupList)
    {
        $groupAuth = false;

        if ($this->conf['fe_groups']) {
            $feGroups = GeneralUtility::trimExplode(',', $this->conf['fe_groups']);
            foreach ($feGroups as $oneGroup) {
                if (GeneralUtility::inList($groupList, $oneGroup)) {
                    $groupAuth = true;
                }
            }
        } else {
            $groupAuth = true;
        }

        return $groupAuth;
    }


    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
