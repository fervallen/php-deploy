<?php

namespace common\components;

use common\models\Deploy as DeployHistory;
use common\models\Project;
use Yii;
use yii\base\Component;

/**
 * Class Deploy
 * @package common\components
 */
class Deploy extends Component
{
    const DEPLOY_LOG_PATH = '/tmp/deploy_project_';

    /**
     * @var array
     */
    private $projects = [];

    /**
     * Deploy constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config['projects'] as $id => $projectData) {
            $this->projects[$id] = new Project($id, $projectData);
        }
        unset($config['projects']);
        parent::__construct($config);
    }

    /**
     * @return bool
     */
    public function hasProjects()
    {
        return !empty($this->projects);
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param int $id
     * @return Project
     */
    public function getProject($id)
    {
        return array_key_exists($id, $this->projects)
            ? $this->projects[$id]
            : null;
    }

    /**
     * @param Project $project
     * @param string $branch
     * @param string $deployType
     * @return array
     */
    public function deploy($project, $branch, $deployType)
    {
        $this->clearDeployProgressFile($project);
        $branchCommands = [
            'cd ' . $project->getPath(),
            'git fetch --all',
        ];
        $activeBranch = $this->getActiveBranch($project);
        if ($branch != $activeBranch) {
            $branchCommands = array_merge($branchCommands, [
                'git reset --hard ' . $activeBranch,
                'git checkout ' . $branch,
            ]);
        }
        $branchCommands[] = 'git reset --hard origin/' . $branch;
        $commands = array_merge($branchCommands, $project->getCommand($deployType));
        exec($this->createExecCommand($commands, $project), $output, $code);
        $output = $this->getDeployProgressOutput($project) . implode("\n", $output);
        $this->clearDeployProgressFile($project);

        return [$output, $code];
    }

    /**
     * @param DeployHistory $deploy
     */
    public function launchConsoleDeploy($deploy)
    {
        shell_exec(
            '(cd ' . __DIR__ . '/../../ && php yii deploy/launch ' . $deploy->id . ')'
            . ' > /dev/null 2>/dev/null &'
        );
    }

    /**
     * @param Project $project
     * @return array
     */
    public function getBranchList($project)
    {
        exec(
            $this->createExecCommand([
                'cd ' . $project->getPath(),
                'git remote update --prune origin >> /dev/null',
                'git branch -r | grep origin/ | grep -v HEAD',
            ]),
            $output,
            $status
        );
        $branchList = [];
        foreach ($output as $outputString) {
            $key = str_replace('  origin/', '', $outputString);
            $branchList[$key] = $key;
        }

        return $branchList;
    }

    /**
     * @param array $commands
     * @param Project|null $project
     * @return string
     */
    private function createExecCommand(array $commands, $project = null)
    {
        $commands = implode(' && ', $commands);
        if ($project) {
            $commands = '(' . $commands . ') 2>&1 >> ' . $this->getDeployLogFileName($project);
        }

        return $commands;
    }

    /**
     * @param Project $project
     * @return string
     */
    public function getDeployProgressOutput($project)
    {
        $output = '';
        if (file_exists($this->getDeployLogFileName($project))) {
            $output = file_get_contents($this->getDeployLogFileName($project));
        }

        $dictionary = ['/\[0;30m(.*?)\[0m/s', '/\[0;31m(.*?)\[0m/s', '/\[0;32m(.*?)\[0m/s',
            '/\[0;34m(.*?)\[0m/s', '/\[0;35m(.*?)\[0m/s', '/\[0;36m(.*?)\[0m/s',
            '/\[0;37m(.*?)\[0m/s', '/\[1;30m(.*?)\[0m/s', '/\[1;31m(.*?)\[0m/s',
            '/\[1;32m(.*?)\[0m/s', '/\[1;33m(.*?)\[0m/s', '/\[1;34m(.*?)\[0m/s',
            '/\[1;35m(.*?)\[0m/s', '/\[1;36m(.*?)\[0m/s', '/\[1;37m(.*?)\[0m/s',
            '/\[0;33m(.*?)\[0m/s', '/\[0;93m(.*?)\[0m/s', '/\[0;91m(.*?)\[0m/s',
            '/\[91m(.*?)\[0m/s',
            '[m', '[0', '[91m', '##', 'Downloading:', 'Connecting...',
            '100%', '1%', '2%', '3%', '4%', '5%', '6%', '7%', '8%', '9%', '0%',
            '11%', '12%', '13%', '14%', '15%', '16%', '17%', '18%', '19%', '10%',
            '21%', '22%', '23%', '24%', '25%', '26%', '27%', '28%', '29%', '20%',
            '31%', '32%', '33%', '34%', '35%', '36%', '37%', '38%', '39%', '30%',
            '41%', '42%', '43%', '44%', '45%', '46%', '47%', '48%', '49%', '40%',
            '51%', '52%', '53%', '54%', '55%', '56%', '57%', '58%', '59%', '50%',
            '61%', '62%', '63%', '64%', '65%', '66%', '67%', '68%', '69%', '60%',
            '71%', '72%', '73%', '74%', '75%', '76%', '77%', '78%', '79%', '70%',
            '81%', '82%', '83%', '84%', '85%', '86%', '87%', '88%', '89%', '80%',
            '91%', '92%', '93%', '94%', '95%', '96%', '97%', '98%', '99%', '90%',
        ];
        $output = str_replace($dictionary, '', $output);
        $output = preg_replace('/ \-\-\-\> (.*)' . "\n" . '/i', '', $output);

        return $output;
    }

    /**
     * @param Project $project
     */
    public function clearDeployProgressFile($project)
    {
        if (file_exists($this->getDeployLogFileName($project))) {
            unlink($this->getDeployLogFileName($project));
        }
    }

    /**
     * @param Project $project
     * @return string
     */
    public function getDeployLogFileName($project)
    {
        return self::DEPLOY_LOG_PATH . $project->getId();
    }

    /**
     * @param Project $project
     * @return bool
     */
    public function inProgress($project)
    {
        $lastDeploy = $project->getLastDeploy();

        return !empty($lastDeploy) && !$lastDeploy->isFinished();
    }

    /**
     * @param Project $project
     * @return string
     */
    public function getActiveBranch($project)
    {
        $branch = exec($this->createExecCommand([
            'cd ' . $project->getPath(),
            'git branch | grep \*',
        ]));

        return str_replace('* ', '', $branch);
    }
}
