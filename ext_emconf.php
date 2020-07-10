<?php

$EM_CONF['authagainsttypo3'] = array(
    'title' => 'auth against TYPO3',
    'description' => 'Provides an CLI command that provides authentication against TYPO3 backend users',
    'category' => 'cli',
    'state' => 'stable',
    'version' => '5.1.0',

    'author' => 'Ingo Schmitt',
    'author_email' => 'typo3@marketing-factory.de',
    'author_company' => 'Marketing Factory Consulting GmbH',

    'constraints' => array(
        'depends' => array(
            'typo3' => '8.7.0-10.4.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
