<?php

// Call the functionality
/** @var \Mfc\Authagainsttypo3\Authentication\ConsoleAuthentication $authentication */
$authentication = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    'Mfc\\Authagainsttypo3\\Authentication\\ConsoleAuthentication'
);
$authentication->main($_SERVER['argv']);
