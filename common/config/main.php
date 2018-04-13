<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'deploy' => [
            'class' => 'common\components\Deploy',
            'projects' => file_exists(__DIR__ . '/deploy-local.php')
                ? require(__DIR__ . '/deploy-local.php')
                : [],
        ],
    ],
];
