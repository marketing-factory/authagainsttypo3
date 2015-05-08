<?php

if (!defined('TYPO3_cliMode')) {
	die('You cannot run this script directly!');
}

/**
 * Class tx_cliAuthBE_cli
 */
class tx_cliAuthBE_cli extends t3lib_cli {

	/**
	 * @var string
	 */
	public $extkey = 'authagainsttypo3';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->cli_setArguments($_SERVER['argv']);

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
	public function authAgainstBe($credentials) {
		/** @var t3lib_db $database */
		$database = $GLOBALS['TYPO3_DB'];
		$user = $database->exec_SELECTgetSingleRow(
			'*',
			'be_users',
			'username = ' . $database->fullQuoteStr($credentials['user'], 'be_users')
		);

		if (empty($user)) {
			$error = 'Invalid user';
			$exit = 1;
		} else {
			$error = 'Invalid password';
			if (t3lib_extMgm::isLoaded('saltedpasswords') && tx_saltedpasswords_div::isUsageEnabled('BE')) {
				$saltObject = NULL;
				if (strpos($user['password'], '$1') === 0) {
					$saltObject = tx_saltedpasswords_salts_factory::setPreferredHashingMethod('tx_saltedpasswords_salts_md5');
				}
				if (!is_object($saltObject)) {
					$saltObject = tx_saltedpasswords_salts_factory::getSaltingInstance($user['password']);
				}
				$exit = !$saltObject->checkPassword($credentials['pass'], $user['password']);
			} else {
				$exit = !(md5($credentials['pass']) == $user['password']);
			}
		}

		if ($exit) {
			print $error;
		} else {
			print 'Valid login';
		}
		exit ((int)$exit);
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
