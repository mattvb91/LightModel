<?php

namespace mattvb91\LightModel\Tests\TestModels;

use mattvb91\LightModel\LightModel;

/**
 * Test class
 *
 * Class Event
 * @package mattvb91\LightModel\Tests\TestModels
 */
class Event extends LightModel
{

    public $name, $date, $description;

    //Test custom primary key (varchar)
    protected $keyName = 'event_id';

    protected $tableName = 'event';

    /**
     * Maps DB Columns (keys) to the associated values.
     * [ db_column => value ]
     *
     * @return array
     */
    public function getValues(): array
    {
        return [
            'event_id'    => $this->getKey(),
            'name'        => $this->name,
            'date'        => $this->date,
            'description' => $this->description,
        ];
    }
}