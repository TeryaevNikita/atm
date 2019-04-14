<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m190414_142515_resource
 */
class m190414_142515_resource extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('resource', [
            'id' => Schema::TYPE_PK,
            'currency' => Schema::TYPE_STRING,
            'denomination' => Schema::TYPE_INTEGER,
            'count' => Schema::TYPE_INTEGER,
            'atm_id' => Schema::TYPE_BIGINT,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('resource');
        echo "m190414_142515_resource cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190414_142515_resource cannot be reverted.\n";

        return false;
    }
    */
}
