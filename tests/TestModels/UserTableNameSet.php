<?php

namespace mattvb91\LightModel\Tests\TestModels;

use mattvb91\LightModel\LightModel;

/**
 * Class UserTableNameSet
 */
class UserTableNameSet extends LightModel
{

    protected $tableName = 'user';

    /**
     * Maps DB Columns (keys) to the associated values.
     * [ db_column => value ]
     *
     * @return array
     */
    public function getValues()
    {
        return [];
    }
}