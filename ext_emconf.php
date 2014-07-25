<?php

########################################################################
# Extension Manager/Repository config file for ext "authagainsttypo3".
#
# Auto generated 17-02-2012 16:59
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Auth Against TYPO3',
	'description' => 'Provides a Webservice which can be used to Athanticate against TYPO3 FE Users',
	'category' => 'fe',
	'author' => 'Ingo Schmitt',
	'author_email' => 'is@marketing-factory.de',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'Marketing Factory Consulting GmbH',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array (
			'typo3' => '4.5.0-6.2.9',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:13:{s:9:"ChangeLog";s:4:"79bc";s:12:"ext_icon.gif";s:4:"27aa";s:17:"ext_localconf.php";s:4:"9768";s:14:"ext_tables.php";s:4:"d10d";s:16:"locallang_db.xml";s:4:"c608";s:23:"cli/class.cliAuthBE.php";s:4:"3251";s:21:"contrib/post-test.php";s:4:"6484";s:14:"doc/manual.sxw";s:4:"d615";s:37:"pi1/class.tx_authagainsttypo3_pi1.php";s:4:"b21b";s:17:"pi1/locallang.xml";s:4:"bfa9";s:24:"pi1/static/editorcfg.txt";s:4:"2f67";s:31:"static/Webservice/constants.txt";s:4:"790a";s:27:"static/Webservice/setup.txt";s:4:"3d72";}',
	'suggests' => array(
	),
);

?>