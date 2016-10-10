<?php
defined('TYPO3_MODE') or die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
    'authagainsttypo3',
    'Classes/Controller/LoginController.php',
    '_login',
    'list_type',
    0
);

if (TYPO3_MODE === 'BE') {
    // registers login at the cli_dispatcher with key "authagainsttypo3".
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['authagainsttypo3'] = array(
        'EXT:authagainsttypo3/Scripts/CommandLineLauncher.php',
        '_CLI_user'
    );
}
