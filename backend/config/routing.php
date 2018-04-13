<?php

return [
    'defaultRoute' => 'site/index',
    'deploy/project/<id:\d+>' => 'deploy/project',
    'deploy/project/<id:\d+>/history' => 'deploy/history',
    'deploy/project/<id:\d+>/start' => 'deploy/start',
    'deploy/project/<id:\d+>/cancel' => 'deploy/cancel',
    'deploy/<id:\d+>/check' => 'deploy/check',
    '<controller>' => '<controller>/index',
    '<controller>/<id:\d+>' => '<controller>/view',
    '<controller>/<action>' => '<controller>/<action>',
    '<controller>/<id:\d+>/<action>' => '<controller>/<action>',
];
