<?php

use backend\assets\AppAsset;
use common\widgets\Alert;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\web\View;

/* @var $this View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
  <head>
      <meta charset="<?= Yii::$app->charset ?>">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <?= Html::csrfMetaTags() ?>
      <title><?= Html::encode($this->title) ?></title>
      <?php $this->head() ?>
  </head>
  <body class="environment-<?= YII_ENV ?>">
    <?php $this->beginBody() ?>
    <div class="wrap">
        <?= $this->render('_top-menu.php') ?>
        <div class="container <?= Yii::$app->controller->id ?>-<?= Yii::$app->controller->action->id ?>">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </div>
    <footer class="footer">
        <div class="container">
          <p class="pull-left">
            &copy; Helpcrunch 2016 - <?= date('Y') ?>
          </p>
          <p class="pull-right">
            Admin panel UTC time:
            <?= (new \DateTime(date('Y-m-d H:i:s'), new \DateTimeZone('UTC')))->format('Y-m-d H:i:s') ?>
            / <b><?= YII_ENV ?></b> environment
          </p>
        </div>
    </footer>
    <?php $this->endBody() ?>
  </body>
</html>
<?php $this->endPage() ?>
