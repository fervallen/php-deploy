<?php

use common\models\Project;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;

/* @var $this View */
/* @var $projects ArrayDataProvider */

$this->title = 'Deploy center';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deploy-index">
  <h1><?= Html::encode($this->title) ?></h1>
    <?= GridView::widget([
        'dataProvider' => $projects,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'content' => function (Project $project) {
                    return Html::a($project->getName(), [
                        'deploy/project',
                        'id' => $project->getId()
                    ]);
                },
            ],
            [
                'attribute' => 'lastDeploy',
                'content' => function (Project $project) {
                    $lastDeploy = $project->getLastDeploy();
                    if (!$lastDeploy) {
                        return 'Never deployed, yet.';
                    }

                    if ($lastDeploy->isFinished()) {
                        if ($lastDeploy->isCanceled()) {
                            return '<b>' . ucfirst($lastDeploy->user->username) . '</b>' .
                                ' canceled <b>' . ucfirst($lastDeploy->branch) . '</b>' .
                                ' branch deploy ' .
                                ' at ' . date(Yii::$app->params['altDateTimeFormat'], $lastDeploy->finished_at);
                        } else {
                            return '<b>' . ucfirst($lastDeploy->branch) . '</b>' .
                                ' branch ' .
                                ($lastDeploy->wasSuccessful() ? ' was deployed ' : 'failed to deploy') .
                                ' by <b>' . ($lastDeploy->user ? ucfirst($lastDeploy->user->username) : 'removed user') . '</b>' .
                                ' at ' . date(Yii::$app->params['altDateTimeFormat'], $lastDeploy->finished_at) .
                                ' (took ' . $lastDeploy->getDuration() . ')';
                        }
                    } else {
                        return '<b>' . ucfirst($lastDeploy->user->username) . '</b>' .
                            ' is deploying ' .
                            '<b>' . $lastDeploy->branch . '</b> branch right now.';
                    }
                },
                'contentOptions' => function (Project $project) {
                    $lastDeploy = $project->getLastDeploy();
                    $class = 'text-muted';
                    if ($lastDeploy && !$lastDeploy->isFinished()) {
                        $class = 'info';
                    } elseif ($lastDeploy && $lastDeploy->isFinished() && $lastDeploy->wasSuccessful()) {
                        $class = 'success';
                    } elseif ($lastDeploy && $lastDeploy->isFinished() && !$lastDeploy->wasSuccessful()) {
                        $class = 'danger';
                    }

                    return [
                        'class' => $class,
                    ];
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{deploy} {history}',
                'buttons' => [
                    'deploy' => function ($url, Project $project) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-play-circle" title="Deploy this project"></span>',
                            ['deploy/project', 'id' => $project->getId()]
                        );
                    },
                    'history' => function ($url, Project $project) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-time" title="View deploy history"></span>',
                            ['deploy/history', 'id' => $project->getId()]
                        );
                    },
                ],
            ],
        ],
    ]) ?>
</div>
