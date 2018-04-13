<?php

use common\models\Deploy;
use common\models\Project;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\View;

/* @var $this View */
/* @var $deploy Deploy */
/* @var $project Project */

$this->title = 'Deploy #' . $deploy->id;
$this->params['breadcrumbs'] = [
    ['label' => 'Deploy', 'url' => ['index']],
    ['label' => $project->getName(), 'url' => ['project', 'id' => $project->getId()]],
    $this->title
];
?>
<div class="deploy-view">
    <?= DetailView::widget([
        'model' => $deploy,
        'attributes' => [
            'id',
            'finished:boolean',
            'canceled:boolean',
            [
                'label' => 'Deploy time',
                'attribute' => 'created_at',
                'format' => Yii::$app->params['timeFormat'],
            ],
            [
                'attribute' => 'finished_at',
                'format' => Yii::$app->params['timeFormat'],
                'visible' => $deploy->isFinished(),
            ],
            [
                'attribute' => 'project',
                'value' => Html::a($project->getName(), ['project', 'id' => $project->getId()]),
                'format' => 'html',
            ],
            [
                'attribute' => 'user_id',
                'value' => Html::a($deploy->user->username, ['user/view', 'id' => $deploy->user->id]),
                'format' => 'html',
            ],
            'branch',
            'type',
            [
                'attribute' => 'code',
                'contentOptions' => [
                    'class' => 'text-' . ($deploy->wasSuccessful() ? 'success' : 'danger')
                ],
            ],
            'output',
        ],
    ]) ?>
</div>
