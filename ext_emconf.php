<?php

$EM_CONF['authagainsttypo3'] = array(
    'title' => 'Auth Against TYPO3',
    'description' => 'Provides a Webservice which can be used to Athanticate against TYPO3 FE Users',
    'category' => 'fe',
    'state' => 'stable',
    'version' => '4.0.1',

    'author' => 'Ingo Schmitt',
    'author_email' => 'typo3@marketing-factory.de',
    'author_company' => 'Marketing Factory Consulting GmbH',

    'constraints' => array(
        'depends' => array(
            'typo3' => '8.7.0-8.99.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
