<?php


namespace mattvb91\LightModel\Tests\TestModels;


use mattvb91\LightModel\LightModel;

class Book extends LightModel
{

    public $name, $user_id;

    protected $tableName = 'books';

    /**
     * Maps DB Columns (keys) to the associated values.
     * [ db_column => value ]
     *
     * @return array
     */
    public function getValues()
    {
        return [
            'name'    => $this->name,
            'user_id' => $this->user_id,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wrongForeignKey()
    {
        return $this->belongsTo(User::class, 'wrong');
    }
}