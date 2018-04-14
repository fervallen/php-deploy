<?php
return [
    [
        'name' => 'Self',
        'path' => str_replace('/common/config', '', __DIR__),
        'commands' => [
            'default' => [
                'composer install',
                'php yii migrate/up --interactive=0',
                'rm -rf backend/runtime/*',
                'rm -rf console/runtime/*',
                'cachetool opcache:reset --fcgi=127.0.0.1:9000',
            ],
            'code-update-only' => [
                'echo "Updated"',
                'rm -rf backend/runtime/*',
                'rm -rf console/runtime/*',
                'cachetool opcache:reset --fcgi=127.0.0.1:9000',
            ],
            'migrate-only' => [
                'php yii migrate/up --interactive=0',
            ],
        ],
    ],
];
