<?php

namespace backend\controllers;

use backend\models\search\DeploySearch;
use common\components\Deploy as DeployComponent;
use common\models\Deploy;
use common\models\Project;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Module;
use yii\data\ArrayDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * DeployController is for... you know... deploy
 */
class DeployController extends Controller
{
    /**
     * @var DeployComponent
     */
    private $deployComponent;

    /**
     * DeployController constructor.
     * @param string $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->deployComponent = Yii::$app->get('deploy');
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Tariff models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (!$this->deployComponent->hasProjects()) {
            $this->redirect(['/']);
        }

        $projects = $this->deployComponent->getProjects();
        $projectsDataProvider = new ArrayDataProvider();
        $projectsDataProvider->setModels($projects);
        $projectsDataProvider->setTotalCount(count($projects));

        return $this->render('index', [
            'projects' => $projectsDataProvider
        ]);
    }

    /**
     * Displays a single project to deploy
     * @param integer $id
     * @return string
     */
    public function actionProject($id)
    {
        $project = $this->findModel($id);
        $lastDeploy = $project->getLastDeploy();

        return $this->render('project', [
            'project' => $project,
            'lastDeploy' => $lastDeploy,
            'deployInProgress' => $lastDeploy && !$lastDeploy->isFinished(),
            'currentBranch' => $this->deployComponent->getActiveBranch($project),
            'currentDeployType' => $lastDeploy ? $lastDeploy->type : 'default',
            'branchList' => $this->deployComponent->getBranchList($project),
        ]);
    }

    /**
     * Displays a single project to deploy
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $deploy = Deploy::findOne(['id' => $id]);
        if (!$deploy) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $project = $this->findModel($deploy->project_id);

        return $this->render('view', [
            'project' => $project,
            'deploy' => $deploy,
        ]);
    }

    /**
     * Launches a deploy
     * @param integer $id
     * @return array|Response
     */
    public function actionStart($id)
    {
        $project = $this->findModel($id);
        if ($this->deployComponent->inProgress($project)) {
            return $this->redirect('/deploy/check/' . $project->getLastDeploy()->id);
        }
        Yii::$app->response->format = 'json';

        if ($branch = Yii::$app->request->post('branch')) {
            $type = Yii::$app->request->post('type') ?? 'default';

            $deploy = new Deploy();
            $deploy->user_id = Yii::$app->getUser()->identity->getId();
            $deploy->project_id = $project->getId();
            $deploy->branch = $branch;
            $deploy->type = $type;
            $deploy->created_at = time();
            $deploy->save(false);

            $this->deployComponent->launchConsoleDeploy($deploy);

            return [
                'finished' => false,
                'id' => $deploy->id,
            ];
        } else {
            throw new InvalidParamException('No branch is specified');
        }
    }

    /**
     * Checks on a deploy
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCheck($id)
    {
        $deploy = Deploy::findOne(['id' => $id]);
        if (!$deploy || !($project = $this->deployComponent->getProject($deploy->project_id))) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        Yii::$app->response->format = 'json';
        if ($deploy->isFinished()) {
            return [
                'finished' => true,
                'code' => $deploy->code,
                'output' => $deploy->output,
                'duration' => $deploy->getDuration(),
                'canceled' => $deploy->isCanceled(),
                'canceled_by' => $deploy->user->username,
                'finished_at' => date(Yii::$app->params['altDateTimeFormat'], $deploy->finished_at),
            ];
        } else {
            return [
                'finished' => false,
                'output' => $this->deployComponent->getDeployProgressOutput($project),
            ];
        }
    }

    /**
     * Cancels a deploy
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCancel($id)
    {
        $deploy = Deploy::findOne(['id' => $id]);
        if (!$deploy || !($project = $this->deployComponent->getProject($deploy->project_id))) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $deploy->user_id = Yii::$app->user->identity->id;
        $deploy->output = $this->deployComponent->getDeployProgressOutput($project)
            . "\n" . 'Canceled by ' . Yii::$app->user->identity->username . "\n";
        $deploy->finished = true;
        $deploy->finished_at = time();
        $deploy->code = -1;
        $deploy->canceled = true;
        $deploy->save();
    }

    /**
     * Displays a deploy history for a project
     * @param integer $id
     * @return mixed
     */
    public function actionHistory($id)
    {
        $project = $this->findModel($id);

        $searchModel = new DeploySearch();
        $params = Yii::$app->request->queryParams;
        $params['DeploySearch']['project_id'] = $id;
        $dataProvider = $searchModel->search($params);

        return $this->render('history', [
            'project' => $project,
            'history' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Finds the Tariff model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Project the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if ($model = $this->deployComponent->getProject($id)) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
