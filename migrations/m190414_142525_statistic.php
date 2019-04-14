<?php

use yii\db\Migration;

/**
 * Class m190414_142525_statistic
 */
class m190414_142525_statistic extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('statistic', [
            'id' => $this->primaryKey(),
            'amount' => $this->integer(),
            'currency' => $this->string(),
            'type' => $this->string(),
            'date' => $this->timestamp(),
            'atm_id' => $this->bigInteger(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('statistic');
        echo "m190414_142525_statistic cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190414_142525_statistic cannot be reverted.\n";

        return false;
    }
    */
}
