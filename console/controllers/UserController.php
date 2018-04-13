<?php

namespace console\controllers;

use Yii;
use common\models\User;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * This is the tools for admin-panel users control.
 *
 * Class UserController
 * @package console\controllers
 */
class UserController extends Controller
{
    public function init()
    {
        $this->color = true;
        parent::init();
    }

    /**
     * Shows the list of users
     */
    public function actionIndex()
    {
        $users = User::find()->all();
        if ($count = count($users)) {
            $this->stdout('Users (' . $count . ')' . "\n", Console::FG_YELLOW);
            foreach ($users as $user) {
                if ($user->status == User::STATUS_ACTIVE) {
                    $this->stdout('#' . $user->id . ' ');
                    $this->stdout($user->username, Console::FG_GREEN);
                    $this->stdout(' from ' . date('d.m.Y', $user->created_at) . "\n");
                }
            }
        } else {
            $this->stderr('No users in DB, yet :(' . "\n", Console::FG_YELLOW);
        }
    }

    /**
     * Creates a new admin user
     */
    public function actionCreate()
    {
        $this->stdout('Adding a new admin user.' . "\n");
        $user = new User();
        $user->username = $this->prompt('Specify username:');
        $user->email = $this->prompt('Enter email:');
        $user->setPassword($this->prompt('Type password:'));
        $user->generateAuthKey();
        $user->save();
        $this->stdout('User created.' . "\n", Console::FG_GREEN);
    }

    /**
     * Creates a new admin user
     */
    public function actionAutoCreate()
    {
        $user = new User();
        $user->username = 'admin';
        $user->email = 'test@example.com';
        $user->setPassword('666666');
        $user->generateAuthKey();
        $user->save();
    }
}
