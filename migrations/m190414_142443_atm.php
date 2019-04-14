<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m190414_142443_atm
 */
class m190414_142443_atm extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('atm', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING,
        ]);

        $this->execute("INSERT INTO atm (name) VALUES ('ATM')");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('atm');
        echo "m190414_142443_atm cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190414_142443_atm cannot be reverted.\n";

        return false;
    }
    */
}
