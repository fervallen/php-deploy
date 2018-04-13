<?php

use common\models\Deploy;
use yii\db\Migration;

class m170405_133546_oh_long_deployson extends Migration
{
    public function safeUp()
    {
        $this->alterColumn(Deploy::tableName(), 'output', $this->text());
    }

    public function safeDown()
    {
        $this->alterColumn(Deploy::tableName(), 'output', $this->string(20000)->null());
    }
}
