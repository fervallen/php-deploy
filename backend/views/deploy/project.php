<?php

use common\models\Deploy;
use common\models\Project;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use yii\web\View;

/* @var $this View */
/* @var $project Project */
/* @var $lastDeploy Deploy|null */
/* @var $deployInProgress bool */
/* @var $currentBranch string */
/* @var $currentDeployType string */
/* @var $branchList string[] */

$this->registerJsFile(
    '@web/js/deploy.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

$title = $project->getName() . ' deploy';
$this->title = $deployInProgress ? 'deploy is in progress' : $title;
$this->params['breadcrumbs'] = [
    ['label' => 'Deploy', 'url' => ['index']],
    $title
];
?>
<div class="deploy-view">
  <h1><?= $title ?></h1>
  <div class="col-md-12">
    <div class="col-md-6">
      <textarea class="deploy-result"><?= $lastDeploy ? $lastDeploy->output : 'No previous deploy' ?></textarea>
      <br/><br/>
    </div>


    <div class="col-md-6">
      <div id="project-info"
          <?php if ($deployInProgress) : ?>
            style="display: none;"
          <?php endif ?>
      >
        <?php
            /**
             * @param Deploy $lastDeploy
             * @param bool $deployInProgress
             * @return string
             */
            function getLastDeployInfo($lastDeploy, $deployInProgress) {
                if (!$lastDeploy) {
                    return '<span class="last-deploy text-muted">Never deployed, yet</span>';
                } elseif($lastDeploy->isCanceled()) {
                    return '<span class="last-deploy text-muted">Canceled by user</span>';
                } elseif($lastDeploy->wasSuccessful()) {
                    return '<span class="last-deploy text-success">Deploy successful (took ' . $lastDeploy->getDuration() . ')</span>';
                } elseif(!$lastDeploy->wasSuccessful()) {
                    return '<span class="last-deploy text-danger">Deploy failed (code ' . $lastDeploy->code . ')</span>';
                } elseif($deployInProgress) {
                    return '<span class="last-deploy text-info">Deploy is in progress</span>';
                } else {
                    return '<span class="last-deploy">-</span>';
                }
            }
        ?>
        <?= DetailView::widget([
            'model' => $project,
            'attributes' => [
                'path',
                [
                    'label' => 'Last deploy',
                    'value' => getLastDeployInfo($lastDeploy, $deployInProgress),
                    'format' => 'html'
                ],
                [
                    'label' => 'Deploy history',
                    'value' => Html::a(
                        'View',
                        ['deploy/history', 'id' => $project->getId()]
                    ),
                    'format' => 'html'
                ]
            ],
        ]) ?>
      </div>

      <div id="deploy-progress"
          <?php if (!$deployInProgress) : ?>
            style="display: none;"
          <?php endif ?>
      >
        <img src="/loader.gif" alt="" />
        <div class="deploy-info">
          <h3>Deploy is in progress.</h3>
          <span id="deploy-user-name">
            <?= ucfirst((($lastDeploy && $deployInProgress) ? $lastDeploy->user : Yii::$app->user->identity)->username) ?>
          </span>
          is deploying
          <span id="branch-name">
              <?= $lastDeploy ? $lastDeploy->branch : $currentBranch ?>
          </span>
          branch.<br/>
          Started at
          <span id="deploy-start-time">
              <?= date(Yii::$app->params['altDateTimeFormat'],
                  $lastDeploy ? $lastDeploy->created_at : time()
              ) ?>
          </span>
        </div>
      </div>

      <div>
        <?php $form = ActiveForm::begin(['id' => 'start-deploy-form']); ?>
          <input type="hidden" value="<?= Yii::$app->user->identity->username ?>" id="current-user-name" />
          <input type="hidden" value="<?= $project->getId() ?>" id="project-id" />
          <input type="hidden" value="<?= $lastDeploy ? $lastDeploy->id : '' ?>" id="deploy-id" />
          <input type="hidden" value="<?= $deployInProgress ? 1 : '' ?>" id="deploy-in-progress" />
          <div class="col-md-6">
            <label for="branch">Select branch:</label><br/>
              <?= Html::dropDownList('branch',
                  $currentBranch,
                  $branchList,
                  ['disabled' => $deployInProgress ? 'disabled' : false]
              ); ?>
            <br/><br/>
              <?= Html::submitButton('Deploy', [
                  'class' => ['btn', 'btn-danger'],
                  'id' => 'deploy-button',
                  'disabled' => $deployInProgress ? 'disabled' : false,
              ]) ?>
              <?= Html::submitButton('Cancel', [
                  'class' => ['btn'],
                  'id' => 'cancel-button',
                  'disabled' => $deployInProgress ? false : 'disabled',
              ]) ?>
          </div>
          <div class="col-md-6">
            <label for="branch">Deploy type</label><br/>
              <?= Html::radioList('type',
                  $currentDeployType,
                  $project->getCommandsTypes(),
                  ['itemOptions' => [
                      'disabled' => $deployInProgress ? 'disabled' : false
                  ]]
              ); ?><br/>
          </div>
        <?php ActiveForm::end(); ?>
      </div>
    </div>
  </div>
</div>
