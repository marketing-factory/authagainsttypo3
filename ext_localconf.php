<?php
defined('TYPO3_MODE') or die('Access denied.');

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
    $_EXTKEY,
    'Classes/Controller/AuthenticateController.php',
    '_pi1',
    'list_type'
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['authagainsttypo3'] = array(
    'EXT:authagainsttypo3/Classes/Command/AuthenticateCommand.php',
    '_CLI_user'
);
