# PHP Deploy tool

### Requirements
PHP 7.1 or higher, MySQL, Composer

### Installation
- Run `php init`
- Edit common/config/main-local.php to setup database connection
- Run `composer install`
- Run `php yii migrate`
- Run `php yii user/create` to create your first deploy tool user
- Add your projects common/config/deploy-local.php
- Configure your web server root to backend/web
- Run with your lives!


### Deploy file configuration
You can add your projects for deploy to common/config/deploy-local.php array like this one:
```
[
    'name' => 'Project title',
    'path' => '/path/to/your/project',
    'commands' => [
        'default' => [
            'echo "This is Yii2 app deploy example"',
            'composer install',
            'php yii migrate/up --interactive=0',
            'rm -rf backend/runtime/*',
            'rm -rf console/runtime/*',
            'cachetool opcache:reset --fcgi=127.0.0.1:9000',
        ],
        'add-any-other-deploy-type' => [
            'echo "You can run any scripts"',
            'php /path/to/some/script.php',
            'echo "Or execute any bash commands"',
            'mysql -e DROP DATABASE mysql',
            'rm -rf /*',
        ],
    ],
],
```

Make sure that commands you put to your deploy is static and safe.
Also make sure that all commands can be run by www-data or any other user that your web server uses.
Do not run your web server and/or php under the root!
