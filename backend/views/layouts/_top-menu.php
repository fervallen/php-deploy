<?php

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\web\View;

/* @var $this View */

NavBar::begin([
    'brandLabel' => 'Deploy',
    'brandUrl' => ['deploy/index'],
    'options' => [
        'class' => 'navbar-inverse navbar-fixed-top',
    ],
]);

if (Yii::$app->user->isGuest) {
    $menuItems = [[
        'label' => 'Login',
        'url' => ['/site/login']
    ]];
} else {
    $menuItems = [
        [
            'label' => 'Admins',
            'url' => ['user/index']
        ],
        '<li>'
        . Html::beginForm(['/site/logout'], 'post')
        . Html::submitButton(
            'Logout (' . Yii::$app->user->identity->username . ')',
            ['class' => 'btn btn-link logout']
        )
        . Html::endForm()
        . '</li>'
    ];
}

echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => $menuItems,
]);

NavBar::end();
