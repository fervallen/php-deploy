<?php

use common\models\Deploy;
use backend\models\search\DeploySearch;
use common\models\Project;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;

/* @var $this View */
/* @var $project Project */
/* @var $searchModel DeploySearch */
/* @var $history ActiveDataProvider */

$this->title = $project->getName() . ' deploy history';
$this->params['breadcrumbs'] = [
    ['label' => 'Deploy', 'url' => ['index']],
    ['label' => $project->getName(), 'url' => ['project', 'id' => $project->getId()]],
    ['label' => 'History']
];
?>
<div class="deploy-history">
  <h1><?= Html::encode($this->title) ?></h1>
    <?= GridView::widget([
        'dataProvider' => $history,
        'filterModel' => $searchModel,
        'pager' => Yii::$app->params['pager'],
        'columns' => [
            [
                'attribute' => 'id',
                'content' => function (Deploy $deploy) {
                    return Html::a(
                        $deploy->id,
                        ['deploy/view', 'id' => $deploy->id]
                    );
                },
            ],
            'finished:boolean',
            'canceled:boolean',
            [
                'label' => 'Deploy time',
                'attribute' => 'created_at',
                'format' => Yii::$app->params['timeFormat'],
            ],
            'duration',
            [
                'label' => 'Deployer',
                'attribute' => 'user_id',
                'content' => function (Deploy $deploy) {
                    return Html::a(
                        ucfirst($deploy->user->username),
                        ['user/view', 'id' => $deploy->user->id]
                    );
                },
            ],
            'branch',
            'type',
            [
                'attribute' => 'code',
                'content' => function (Deploy $deploy) {
                    return $deploy->wasSuccessful() ? 'Success' : 'Failed (code ' . $deploy->code . ')';
                },
                'contentOptions' => function (Deploy $deploy) {
                    return [
                        'class' => ($deploy->wasSuccessful() ? 'success' : 'danger')
                    ];
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
            ],
        ],
    ]) ?>
</div>
