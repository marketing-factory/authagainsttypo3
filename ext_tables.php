<?php
defined('TYPO3_MODE') or die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'authagainsttypo3',
    'Configuration/TypoScript',
    'Auth Webservice'
);

/**
 * Page TypoScript for mod wizards
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
    <INCLUDE_TYPOSCRIPT: source="FILE:EXT:authagainsttypo3/Configuration/TsConfig/ModWizards.ts">
');
