<?php

if (!defined('TYPO3_cliMode'))  die('You cannot run this script directly!');

// Include basis cli class
require_once(PATH_t3lib.'class.t3lib_cli.php');
#equire_once(PATH_t3lib.'class.t3lib_htmlmail.php');
#require_once(PATH_t3lib. 'class.t3lib_beUserAuth.php');

class tx_cliAuthBE_cli extends t3lib_cli {

	var $extkey = 'authagainsttypo3';
	/**
	 * Constructor
	 */
	function tx_cliAuthBE_cli () {

		// Running parent class constructor
		parent::t3lib_cli();

		// Setting help texts:
		$this->cli_help['name'] = 'cliAuthBE';
		$this->cli_help['synopsis'] = '###OPTIONS###';
		$this->cli_help['description'] = 'Auth CLI Script for using mod-auth-external, USER and PASS must be set via Environement';
		$this->cli_help['examples'] = '/.../cli_dispatch.phpsh EXTKEY TASK';
		$this->cli_help['author'] = 'Ingo Schmitt, (c) 2012';
	}

	/**
	 * CLI engine
	 *
	 * @param    array        Command line arguments
	 * @return    string
	 */
	function cli_main($argv) {
		 
		// get task (function)
		$task = (string)$this->cli_args['_DEFAULT'][1];

		if (!$task){
			$this->cli_validateArgs();
			$this->cli_help();
			exit;
		}
	
		if ($task == 'authENV') {
			$credentials = $this->getEnv();
			$this->authAgainstBE($credentials);
		}
		
		if ($task == 'authPARAM') {
			$credentials = $this->getCliArgs();
			$this->authAgainstBE($credentials);
		}
		
	}
	
	function authAgainstBE($credentials) {
		
		/**
		 * Quickhack direct to database
		 */
		#mail('is@web-factory.de','deb1',print_r($credentials,true));
		$GLOBALS['TYPO3_DB']->debugOutput = 1;
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('password,uid','be_users','username = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($credentials['user'],'be_users') . ' and deleted=0 AND disable=0 ');
		if (count($result ===1)){
			
			if ($result[0]['password'] === md5($credentials['pass'])) {
				print "Valid User";
				exit(0);
			
			}
			
		}
		print "Invalid User";
		exit (1);
		/*
		t3lib_div::devLog('Start Login for BE', $this->extkey,0);
		
		$subType = 'getUserBE';
		while (is_object($serviceObj = t3lib_div::makeInstanceService('auth', $subType, $serviceChain))) {
			$serviceChain.=','.$serviceObj->getServiceKey();
			$serviceObj->initAuth($subType, $loginData, $authInfo, $this);
			if ($row=$serviceObj->getUser()) {
				$tempuserArr[] = $row;
		
				if ($this->writeDevLog) 	t3lib_div::devLog('User found: '.t3lib_div::arrayToLogString($row, array($this->userid_column,$this->username_column)), 't3lib_userAuth', 0);
		
				// user found, just stop to search for more if not configured to go on
				if(!$this->svConfig['setup'][$this->loginType.'_fetchAllUsers']) {
					break;
				}
			}
			unset($serviceObj);
		}
		unset($serviceObj);
		
		die();
		$BE_USER = t3lib_div::makeInstance('t3lib_beUserAuth');	
		$BE_USER->dontSetCookie = TRUE;
		$BE_USER->start();
		
		
		print_r($this->lok_be_user);
		*/
		
	}
	
	
	function getEnv() {
		$user = trim(getenv('USER'));
		$pass = trim(getenv('PASS'));
		
		return array ('user' => $user, 'pass' => $pass);
	}
	
	function getCliArgs() {
		$user = trim($this->cli_args['_DEFAULT'][2]);
		$pass = trim($this->cli_args['_DEFAULT'][3]);
	
		return array ('user' => $user, 'pass' => $pass);
	}


}
// Call the functionality
$cleanerObj = t3lib_div::makeInstance('tx_cliAuthBE_cli');
$cleanerObj->cli_main($_SERVER['argv']);

?>
