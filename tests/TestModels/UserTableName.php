<?php

namespace mattvb91\LightModel\Tests\TestModels;

use mattvb91\LightModel\LightModel;

/**
 * Class UserTableNameSet
 */
class UserTableName extends LightModel
{

    /**
     * Maps DB Columns (keys) to the associated values.
     * [ db_column => value ]
     *
     * @return array
     */
    public function getValues(): array
    {
        return [];
    }
}