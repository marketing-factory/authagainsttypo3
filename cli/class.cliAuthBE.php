<?php

if (!defined('TYPO3_cliMode')) {
	die('You cannot run this script directly!');
}

/**
 * Class tx_cliAuthBE_cli
 */
class tx_cliAuthBE_cli extends t3lib_cli {
	public $extkey = 'authagainsttypo3';

	/**
	 * An instance of the salted hashing method.
	 * This member is set in the getSaltingInstance() function.
	 *
	 * @var \TYPO3\CMS\Saltedpasswords\Salt\AbstractSalt
	 */
	protected $objInstanceSaltedPW = NULL;

	/** @var float  */
	public $t3Version = '4.0';

	/**
	 * Constructor
	 */
	public function __construct() {

		// Running parent class constructor
		$this->t3Version = floatval($GLOBALS['TYPO3_VERSION']?$GLOBALS['TYPO3_VERSION']:
				$GLOBALS['TYPO_VERSION'] ? $GLOBALS['TYPO_VERSION'] :$GLOBALS['TYPO3_CONF_VARS']['SYS']['compat_version']);

		if ($this->t3Version >= 6.0) {
			parent::__construct();
		} else {
			parent::t3lib_cli();
		}

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
	public function cliMain($argv) {
		// get task (function)
		$task = (string)$this->cli_args['_DEFAULT'][1];

		if (!$task) {
			$this->cli_validateArgs();
			$this->cli_help();
			exit;
		}

		if ($task == 'authENV') {
			$credentials = $this->getEnv();
			$this->authAgainstBe($credentials);
		}

		if ($task == 'authPARAM') {
			$credentials = $this->getCliArgs();
			$this->authAgainstBe($credentials);
		}
	}

	/**
	 * @param $credentials
	 * @return void
	 */
	public function authAgainstBe($credentials) {
		/** @var t3lib_db $database */
		$database = $GLOBALS['TYPO3_DB'];

		$result = $database->exec_SELECTgetRows(
			'password, uid',
			'be_users',
			'username = ' . $database->fullQuoteStr($credentials['user'], 'be_users') . ' AND deleted=0 AND disable=0 '
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
$cleanerObj->cliMain($_SERVER['argv']);
