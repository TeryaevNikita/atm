<?php

namespace app\models\database;

use yii\db\ActiveRecord;

class AtmRecord extends ActiveRecord
{
    public static function tableName()
    {
        return 'atm';
    }
}