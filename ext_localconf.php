<?php
defined('TYPO3_MODE') or die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
    'authagainsttypo3',
    'Classes/Controller/LoginController.php',
    '_login',
    'list_type',
    0
);
