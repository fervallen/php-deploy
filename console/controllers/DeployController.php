<?php

namespace console\controllers;

use common\models\Deploy;
use common\components\Deploy as DeployComponent;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;

/**
 * This is the tools for admin-panel users control.
 *
 * Class UserController
 * @package console\controllers
 */
class DeployController extends Controller
{
    /**
     * @var DeployComponent
     */
    private $deployComponent;

    public function init()
    {
        $this->deployComponent = Yii::$app->get('deploy');
        $this->color = true;
        parent::init();
    }

    /**
     * Shows the list of users
     */
    public function actionIndex()
    {
        $projects = $this->deployComponent->getProjects();
        if ($count = count($projects)) {
            $this->stdout('Projects for deploy (' . $count . '):' . "\n", Console::FG_YELLOW);
            foreach ($projects as $project) {
                $this->stdout('#' . $project->getId() . ' ');
                $this->stdout($project->getName(), Console::FG_GREEN);
                if ($lastDeploy = $project->getLastDeploy()) {
                    $this->stdout(' deployed on ' . date('d.m.Y', $lastDeploy->created_at));
                }
                $this->stdout("\n");
            }
        } else {
            $this->stderr('No projects to deploy on this server' . "\n", Console::FG_YELLOW);
        }
    }

    /**
     * Launches a new deploy
     * @param int|null $deployId
     * @throws Exception
     */
    public function actionLaunch($deployId = null)
    {
        if (!$deployId) {
            $deployId = $this->prompt('Please, enter deploy ID:');
        }

        $deploy = Deploy::findOne(['id' => $deployId]);
        if (!$deploy) {
            throw new Exception('Invalid deploy ID');
        }
        if ($deploy->isFinished()) {
            $this->finishDeploy($deploy, 'Deploy is already finished', -1);
        }

        $project = $this->deployComponent->getProject($deploy->project_id);
        if (!$project) {
            $this->finishDeploy($deploy, 'Project is not found', -1);
        }
        if ($deploy != $project->getLastDeploy()) {
            $this->finishDeploy($deploy, 'That is not the last deploy', -1);
        }

        list($output, $code) = $this->deployComponent->deploy($project, $deploy->branch, $deploy->type);

        $this->finishDeploy($deploy, $output, $code);
        $this->stdout(
            $project->getName() . ' deploy #' . $deploy->id . ' successfully finished.' . "\n",
            Console::FG_GREEN
        );
    }

    /**
     * @param Deploy $deploy
     * @param string $output
     * @param int $code
     * @throws Exception
     */
    private function finishDeploy($deploy, $output, $code)
    {
        if (!$deploy->output) {
            $deploy->output = $output;
        }
        if ($deploy->code === null) {
            $deploy->code = $code;
        }
        $deploy->finished = true;
        $deploy->finished_at = time();
        $deploy->save(false);

        if (!$deploy->wasSuccessful()) {
            throw new Exception($output . ' (code ' . $code . ')');
        }
    }
}
