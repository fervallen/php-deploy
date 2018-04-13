<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "deploys".
 *
 * @property integer $id
 * @property integer $project_id
 * @property integer $user_id
 * @property string $branch
 * @property string $type
 * @property string $output
 * @property integer $code
 * @property bool $canceled
 * @property bool $finished
 * @property integer $created_at
 * @property integer $finished_at
 *
 * @property User $user
 */
class Deploy extends ActiveRecord
{
    const DEPLOY_STATUS_OK = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'deploys';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'user_id', 'branch', 'type', 'created_at'], 'required'],
            [['project_id', 'user_id', 'code', 'created_at', 'finished_at'], 'integer'],
            [['finished', 'canceled'], 'boolean'],
            [['branch', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project ID',
            'user_id' => 'User ID',
            'output' => 'Output',
            'branch' => 'Branch',
            'type' => 'Deploy type',
            'code' => 'Code',
            'canceled' => 'Is canceled',
            'finished' => 'Is finished',
            'created_at' => 'Created At',
            'finished_at' => 'Finished At',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return bool
     */
    public function wasSuccessful()
    {
        return $this->code === self::DEPLOY_STATUS_OK;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->finished;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->canceled;
    }

    /**
     * @return string
     */
    public function getDuration()
    {
        if (!$this->isFinished()) {
            return '-';
        }

        $time = $this->finished_at - $this->created_at;
        if ($time > 60) {
            $time = floor($time / 60) . 'm ' . ($time % 60) . 's';
        } else {
            $time .= 's';
        }

        return $time;
    }
}
