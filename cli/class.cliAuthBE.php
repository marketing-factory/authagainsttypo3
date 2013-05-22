<?php

if (!defined('TYPO3_cliMode')) {
	die('You cannot run this script directly!');
}

	// Include basis cli class
require_once(PATH_t3lib . 'class.t3lib_cli.php');

class tx_cliAuthBE_cli extends t3lib_cli {
	/**
	 * @var string
	 */
	public $extkey = 'authagainsttypo3';

	/**
	 * Constructor
	 */
	public function tx_cliAuthBE_cli() {

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
	 * @param array $argv Command line arguments
	 * @return string
	 */
	public function cli_main($argv) {
			// get task (function)
		$task = (string) $this->cli_args['_DEFAULT'][1];

		if (!$task) {
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

	/**
	 * @param $credentials
	 * @return void
	 */
	public function authAgainstBE($credentials) {
		/** @var t3lib_db $database */
		$database = & $GLOBALS['TYPO3_DB'];

		/**
		 * Quickhack direct to database
		 */
		$result = $database->exec_SELECTgetRows(
			'password,uid',
			'be_users',
			'username = ' . $database->fullQuoteStr($credentials['user'], 'be_users') . ' and deleted=0 AND disable=0 '
		);

		if (count($result) === 1) {
			if ($result[0]['password'] === md5($credentials['pass'])) {
				print 'Valid User';
				exit(0);
			}
		}

		print 'Invalid User';
		exit (1);
	}

	/**
	 * @return array
	 */
	public function getEnv() {
		$user = trim(getenv('USER'));
		$pass = trim(getenv('PASS'));

		return array ('user' => $user, 'pass' => $pass);
	}

	/**
	 * @return array
	 */
	public function getCliArgs() {
		$user = trim($this->cli_args['_DEFAULT'][2]);
		$pass = trim($this->cli_args['_DEFAULT'][3]);

		return array ('user' => $user, 'pass' => $pass);
	}
}

	// Call the functionality
$cleanerObj = t3lib_div::makeInstance('tx_cliAuthBE_cli');
$cleanerObj->cli_main($_SERVER['argv']);

?>