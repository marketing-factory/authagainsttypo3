<?php

$EM_CONF['authagainsttypo3'] = array(
    'title' => 'Auth Against TYPO3',
    'description' => 'Provides a Webservice which can be used to Athanticate against TYPO3 FE Users',
    'category' => 'fe',
    'state' => 'stable',
    'version' => '3.0.0',

    'author' => 'Ingo Schmitt',
    'author_email' => 'typo3@marketing-factory.de',
    'author_company' => 'Marketing Factory Consulting GmbH',

    'constraints' => array(
        'depends' => array(
            'typo3' => '6.2.0-7.99.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
