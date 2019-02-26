<?php
/**
 * Created by PhpStorm.
 * User: sfs
 * Date: 20.09.18
 * Time: 11:12
 */

namespace Mfc\Authagainsttypo3\Controller;


use Mfc\Authagainsttypo3\Authentication\ConsoleAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AuthentificationCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{
	/**
	 * Execute a single upgrade wizard
	 *
	 * @param string $identifier Identifier of the wizard that should be executed
	 */
	public function loginCommand($identifier) {
		/**
		 * @var ConsoleAuthentication $consoleAuth
		 */
		$consoleAuth = GeneralUtility::makeInstance(ConsoleAuthentication::class);
		switch ($identifier) {
			case 'authENV':
				$consoleAuth->authENV();
				break;

			default:
				echo "Authentification is only possible over identifier authENV";
				exit;
		}

	}
}