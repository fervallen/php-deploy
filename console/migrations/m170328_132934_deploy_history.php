<?php

use common\models\Deploy;
use yii\db\Migration;

class m170328_132934_deploy_history extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(Deploy::tableName(), [
            'id' => $this->primaryKey(),
            'project_id' => $this->smallInteger()->notNull(),
            'user_id' => $this->smallInteger()->notNull(),
            'output' => $this->string(20000)->null(),
            'branch' => $this->string(255)->notNull(),
            'type' => $this->string(255)->notNull(),
            'code' => $this->smallInteger()->null(),
            'canceled' => $this->boolean()->defaultValue(false)->notNull(),
            'finished' => $this->boolean()->defaultValue(false)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'finished_at' => $this->integer()->null(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable(Deploy::tableName());
    }
}
