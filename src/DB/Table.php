<?php


namespace mattvb91\LightModel\DB;

use mattvb91\LightModel\Exceptions\ColumnMissingException;
use PDO;

/**
 * Class Table
 * @package mattvb91\LightModel\DB
 */
class Table
{

    /**
     * @var String
     */
    private $name;

    /**
     * @var array
     */
    private $columns = [];

    /**
     * Table constructor.
     * @param string $tableName
     */
    function __construct(string $tableName)
    {
        $this->name = $tableName;
        $this->describe();
    }

    /**
     * Describe the associated table columns and set up our
     * $tableColumns
     */
    private function describe()
    {
        $sql = 'DESCRIBE ' . $this->name;
        $query = DB::getConnection()->prepare($sql);
        $query->execute();

        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $values)
        {
            $this->columns[$values['Field']] = new Column($values);
        }
    }

    /**
     * Get the columns associated with this table
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param $column
     * @return bool
     */
    public function hasColumn($column): bool
    {
        return array_key_exists($column, $this->columns);
    }

    /**
     * @param string $column
     * @return Column
     * @throws ColumnMissingException
     */
    public function getColumn(string $column): Column
    {
        if (! $this->hasColumn($column))
            throw new ColumnMissingException();

        return $this->columns[$column];
    }
}