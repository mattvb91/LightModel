<?php

namespace mattvb91\LightModel\Tests\TestModels;

use mattvb91\LightModel\LightModel;

class User extends LightModel
{
    public $username;

    protected $tableName = 'user';

    /**
     * Maps DB Columns (keys) to the associated values.
     * [ db_column => value ]
     *
     * @return array
     */
    public function getValues()
    {
        return [
            'username' => $this->username,
        ];
    }
}