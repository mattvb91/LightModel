<?php

namespace mattvb91\LightModel\Tests\TestModels;

use mattvb91\LightModel\LightModel;

class User extends LightModel
{

    public $username;

    protected $tableName = 'user';

    public function books()
    {
        return $this->hasMany(Book::class, 'user_id');
    }

    /**
     * Maps DB Columns (keys) to the associated values.
     * [ db_column => value ]
     *
     * @return array
     */
    public function getValues(): array
    {
        return [
            'username' => $this->username,
        ];
    }
}