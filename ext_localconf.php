<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY,'pi1/class.tx_authagainsttypo3_pi1.php','_pi1','list_type',1);

$TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array('EXT:' . $_EXTKEY . '/cli/class.cliAuthBE.php', '_CLI_user');
?>