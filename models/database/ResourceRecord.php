<?php

namespace app\models\database;

use yii\db\ActiveRecord;

class ResourceRecord extends ActiveRecord
{
    public static function tableName()
    {
        return 'resource';
    }
}