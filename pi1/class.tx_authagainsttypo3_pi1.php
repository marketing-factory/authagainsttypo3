<?php
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

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Auth Against TYPO3' for the 'authagainsttypo3' extension.
 *
 * @author	Ingo Schmitt <is@marketing-factory.de>
 * @package	TYPO3
 * @subpackage	tx_authagainsttypo3
 */
class tx_authagainsttypo3_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_authagainsttypo3_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_authagainsttypo3_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'authagainsttypo3';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		# Read Version information by loading ext_emconf.php
		$_EXTKEY = 'authagainsttypo3';
		require_once(t3lib_extMgm::extPath('authagainsttypo3').'ext_emconf.php');

		
		$this->version = $EM_CONF['authagainsttypo3']['version'];
		if (empty($this->version)) {
			$this->version = '0.0.0';
		}
		
		/**
		 * build XML by hand is faster than using XML render methods
		 *
		 */
		$xml= "<?xml version=\"1.0\" encoding=\"".$GLOBALS['TSFE']->renderCharset."\"?>\r\n";
		
		$xml.="<typo3>";
		$xml.="<version nr='".$this->version."' />\r\n";
		
		/**
		 * In a later Version, we could integrate a Version Check for the Auth Service,
		 * when a new Version will be released with an new XML Structure
		 */
		
		
		/**
		 * Acces-Check. Set IP, User and passwort in TS Constants
		 */
		
		if ( t3lib_div::cmpIP($_SERVER['REMOTE_ADDR'],$this->conf['remoteIp']) &&
			(t3lib_div::_POST('serviceUser') == $this->conf['serviceUser']) && 
			(t3lib_div::_POST('servicePass') == $this->conf['servicePass']) 
				) {
			
			/*
			 * Intiantate lokal FE_user for auth
			 */
					
			$this->lok_fe_user = t3lib_div::makeInstance('tslib_feUserAuth');

			$this->lok_fe_user->lockIP = $this->TYPO3_CONF_VARS['FE']['lockIP'];
			$this->lok_fe_user->lockHashKeyWords = $this->TYPO3_CONF_VARS['FE']['lockHashKeyWords'];
			$this->lok_fe_user->checkPid = $this->TYPO3_CONF_VARS['FE']['checkFeUserPid'];
			$this->lok_fe_user->lifetime = intval($this->TYPO3_CONF_VARS['FE']['lifetime']);
			$this->lok_fe_user->checkPid_value = $GLOBALS['TYPO3_DB']->cleanIntList($this->conf['pid']);	// List of pid's acceptable
			
		
			$this->lok_fe_user->dontSetCookie=1;
		
	
			$this->lok_fe_user->start();
			$this->lok_fe_user->unpack_uc('');
			$this->lok_fe_user->fetchSessionData();	// Gets session data
			$recs = t3lib_div::_GP('recs');
			if (is_array($recs))	{	// If any record registration is submitted, register the record.
				$this->lok_fe_user->record_registration($recs, $this->TYPO3_CONF_VARS['FE']['maxSessionDataSize']);
			}
			$this->lok_fe_user->fetchGroupData ( );
			
			/*
			 * calculate Groups
			 */
			
			$gr_list = '0,-2';
			if (is_array($this->lok_fe_user->user) && count($this->lok_fe_user->groupData['uid']))	{
				
				$gr_array = $this->lok_fe_user->groupData['uid'];
				$gr_array = array_unique($gr_array);	// Make unique...
				sort($gr_array);	// sort
				if (count($gr_array))	{
					$gr_list.=','.implode(',',$gr_array);
				}
			}
			$groupAuth = false;
			if (!$this->conf['fe_groups']) {
							
				foreach (t3lib_div::trimExplode(',',$this->conf['fe_groups']) as $oneGroup) {
					if (t3lib_div::inList( $this->lok_fe_user->user['usergroup'],$oneGroup)) {
						$groupAuth = true;
					}
				}
			}else{
				$groupAuth = true;
			}
				
		
			
			if (is_array($this->lok_fe_user->user)){
				if ($this->conf['fields']) {
					$fields = t3lib_div::trimExplode(',',$this->conf['fields']);
				}else{
					$fields = array_keys($this->lok_fe_user->user);
				}
				foreach ($fields as $oneField) {
					$user[trim($oneField)] = $this->lok_fe_user->user[trim($oneField)];
				}
				$xml .=t3lib_div::array2xml($user,'',0,'fe_user'); 
			}else{
				$xml .="<error id='1000'>The given TYPO3 Username and Passwort did not match for the configured Storage PID</error>";
			}
		
		}else{
			$xml .= "<error id='9000'>You are not allowed to use this service</error>";
		}
		$xml.="</typo3>";
	
		return($xml);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/authagainsttypo3/pi1/class.tx_authagainsttypo3_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/authagainsttypo3/pi1/class.tx_authagainsttypo3_pi1.php']);
}

?>