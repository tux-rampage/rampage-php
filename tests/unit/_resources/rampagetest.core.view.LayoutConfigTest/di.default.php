<?php

use Zend\EventManager\ResponseCollection;

return array(
    'mocks' => array(
        'Zend\EventManager\EventManagerInterface' => array(
            'trigger' => function() { return new ResponseCollection(); },
            'triggerUntil' => function() { return new ResponseCollection(); },
        )
    )
);