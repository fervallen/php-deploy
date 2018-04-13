<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Admin panel users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('Add another one', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'attribute' => 'username',
                'content' => function (User $user) {
                    return Html::a($user->username, ['view', 'id' => $user->id]);
                }
            ],
            'email:email',
            [
                'attribute' => 'status',
                'content' => function (User $user) {
                    return $user->isActive() ? 'Enabled' : 'Deleted';
                },
                'contentOptions' => function (User $user) {
                    $options = [];
                    if ($user->isActive()) {
                        $options['class'] = 'text-success';
                    }

                    return $options;
                },
                'label' => 'Status'
            ],
            [
                'attribute' => 'created_at',
                'format' => Yii::$app->params['timeFormat'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'delete' => function ($url, User $user) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-off" title="Disable/Enable User"></span>',
                            ['user/toggle', 'id' => $user->id]
                        );
                    }
                ],
            ],
        ],
    ]); ?>
</div>
