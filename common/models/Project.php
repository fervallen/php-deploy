<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecordInterface;

/**
 * This is the model for deployment projects.
 */
class Project extends Model
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $commands;

    /**
     * Project constructor.
     * @param int $id
     * @param array $params
     * @param array $config
     */
    public function __construct($id, array $params, array $config = [])
    {
        parent::__construct($config);
        $this->id = $id;
        $this->name = $params['name'];
        $this->path = $params['path'];
        $this->commands = $params['commands'];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['commands', 'array'],
            [['path', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'path' => 'Path to project',
            'commands' => 'Commands to be executed',
        ];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getCommandsTypes()
    {
        $commandTypes = [];
        foreach ($this->commands as $commandType => $commands) {
            $commandTypes[$commandType] = str_replace('-', ' ', ucfirst($commandType));
        }

        return $commandTypes;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getCommand($type = 'default')
    {
        return $this->commands[$type] ?? [];
    }

    /**
     * @return Deploy[]|static[]
     */
    public function getDeploys()
    {
        return Deploy::findAll(['project_id' => $this->id]);
    }

    /**
     * @return Deploy|ActiveRecordInterface
     */
    public function getLastDeploy()
    {
        return Deploy::find()
            ->where(['project_id' => $this->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();
    }
}
