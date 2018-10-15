<?php


namespace mattvb91\LightModel;

use Exception;
use mattvb91\LightModel\DB\Column;
use mattvb91\LightModel\DB\DB;
use mattvb91\LightModel\DB\Table;
use mattvb91\LightModel\Exceptions\ColumnMissingException;
use PDO;

/**
 * Class LightModel
 * @package mattvb91\LightModel
 */
abstract class LightModel
{

    /**
     * The unique identifier column for this table.
     *
     * @var string
     */
    protected $keyName = 'id';

    /**
     * The unique identifier.
     *
     * @var int|string
     */
    protected $key;

    /**
     * The associated table name.
     * If not set the class name is used.
     *
     * @var String
     */
    protected $tableName;

    /**
     * @var array
     */
    private static $initOptions = [];

    /**
     * List of associated entities this item belongs to.
     *
     * @var array
     */
    private $_belongsTo = [];

    /**
     * List of items this item is associated with.
     *
     * @var array
     */
    private $_hasMany = [];

    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return DB::getModelTable($this);
    }

    /**
     * Typecast the models columns to the associated mysql data types.
     *
     * @param LightModel $model
     * @return LightModel
     *
     * TODO implement typecasting to other types
     */
    private static function typeCastModel(LightModel $model)
    {
        if (in_array(self::OPTIONS_TYPECAST, self::$initOptions))
        {
            foreach ($model->getTable()->getColumns() as $column)
            {
                /* @var $column Column */
                if (in_array($column->getField(), get_object_vars($model)))
                {
                    if ($column->getType() === Column::TYPE_INT)
                    {
                        $field = $column->getField();
                        settype($model->$field, Column::TYPE_INT);
                    }
                }
            }
        }

        return $model;
    }

    /**
     * Maps DB Columns (keys) to the associated values.
     * [ db_column => value ]
     *
     * @return array
     */
    abstract public function getValues(): array;


    //Typecast model attributes to mysql data types
    const OPTIONS_TYPECAST = 1;

    /**
     * Set up our options and pass PDO to our DB Singleton
     *
     * @param PDO $pdo
     * @param array $options
     */
    public static function init(PDO $pdo, $options = [])
    {
        DB::init($pdo);
        self::$initOptions = $options;
    }

    /**
     * Check if tableName has been set or return based on class;
     * @return String
     */
    public function getTableName(): string
    {
        if ($this->tableName === null)
        {
            $this->tableName = (new \ReflectionClass($this))->getShortName();
        }

        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return $this->keyName;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        $keyname = $this->keyName;

        return $this->$keyname;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $keyName = $this->getKeyName();
        $this->$keyName = $key;
    }

    /**
     * @param $key
     * @return LightModel
     */
    public static function getOneByKey($key): ?LightModel
    {
        /* @var $class LightModel */
        $className = get_called_class();
        $class = new $className;
        $tableKey = $class->getKeyName();

        $sql = 'SELECT * FROM ' . $class->getTableName() . ' WHERE ' . $tableKey . ' = :key';
        $query = DB::getConnection()->prepare($sql);
        $query->execute(['key' => $key]);

        if ($res = $query->fetchObject($className))
        {
            return self::typeCastModel($res);
        }

        return null;
    }

    const FILTER_ORDER = 'order';
    const FILTER_LIMIT = 'limit';


    /**
     * Parse filters & return the associated params for passing into PDO query
     *
     * @param String $sql
     * @param $filter
     * @param LightModel $class
     * @return array
     */
    private static function handleFilter(String &$sql, $filter, LightModel $class): array
    {
        $params = [];

        if (isset($filter[self::FILTER_ORDER]))
        {
            $order = $filter[self::FILTER_ORDER];
            unset($filter[self::FILTER_ORDER]);
        }

        if (isset($filter[self::FILTER_LIMIT]))
        {
            $limit = (int) $filter[self::FILTER_LIMIT];
            unset($filter[self::FILTER_LIMIT]);
        }

        foreach ($filter as $filter => $value)
        {
            if (! $class->getTable()->hasColumn($filter))
                continue;

            //Default operator for all queries
            $operator = '=';

            //Check if the operator was passed in
            if (is_array($value))
            {
                $operator = $value[0];
                $value = $value[1];
            }

            switch ($class->getTable()->getColumn($filter)->getType())
            {
                case Column::TYPE_INT:
                    $value = (int) $value;
                    break;
                default:
                    $value = (string) $value;
            }

            $sql .= ' AND `' . $filter . '` ' . $operator . ' :' . $filter;
            $params[':' . $filter] = $value;
        }

        if (isset($order))
        {
            $sql .= ' ORDER BY ' . $order;
        }

        if (isset($limit))
        {
            $sql .= ' LIMIT ' . $limit;
        }

        return $params;
    }

    /**
     * Get all items based on filter
     *
     * @param array $filter
     * @return array
     */
    public static function getItems($filter = []): array
    {
        $res = [];

        /* @var $class LightModel */
        $className = get_called_class();
        $class = new $className;

        $sql = 'SELECT * FROM ' . $class->getTableName() . ' WHERE 1=1';
        $params = self::handleFilter($sql, $filter, $class);

        $query = DB::getConnection()->prepare($sql);
        $query->execute($params);

        foreach ($query->fetchAll(PDO::FETCH_CLASS, $className) as $item)
        {
            $res[] = self::typeCastModel($item);
        }

        return $res;
    }

    /**
     * Get array of keys that match the specified filters.
     * Can be used when loading large quantities of models is not an option.
     *
     * @param array $filter
     * @return array
     */
    public static function getKeys($filter = []): array
    {
        /* @var $class LightModel */
        $className = get_called_class();
        $class = new $className;
        $tableKey = $class->getKeyName();

        $sql = 'SELECT ' . $tableKey . ' FROM ' . $class->getTableName() . ' WHERE 1=1';
        $params = self::handleFilter($sql, $filter, $class);

        $query = DB::getConnection()->prepare($sql);
        $query->execute($params);

        $res = [];

        foreach ($query->fetchAll() as $key => $value)
        {
            $res[] = $value[0];
        }

        return $res;
    }

    /**
     * Count items based on filter
     *
     * @param array $filter
     * @return int
     */
    public static function count($filter = []): int
    {
        /* @var $class LightModel */
        $className = get_called_class();
        $class = new $className;
        $tableKey = $class->getKeyName();

        $sql = 'SELECT COUNT(' . $tableKey . ') FROM ' . $class->getTableName() . ' WHERE 1=1';
        $params = self::handleFilter($sql, $filter, $class);

        $query = DB::getConnection()->prepare($sql);
        $query->execute($params);

        return (int) $query->fetchColumn(0);
    }


    /**
     * Check does the current item exist.
     *
     * @return bool
     */
    public function exists(): bool
    {
        $tableKey = $this->getKeyName();

        //If key isn't set we cant associate this record with DB
        if (! isset($this->$tableKey))
        {
            return false;
        }

        $sql = 'SELECT EXISTS(SELECT ' . $tableKey . ' FROM ' . $this->getTableName() . ' WHERE ' . $this->getKeyName() . ' = :key LIMIT 1)';
        $query = DB::getConnection()->prepare($sql);
        $query->execute(['key' => $this->getKey()]);

        return boolval($query->fetchColumn(0));
    }

    /**
     * Reload the current Model
     *
     * TODO fetchColumns instead of self::getOneByKey() to only update column values
     */
    public function refresh()
    {
        $keyName = $this->keyName;

        //If we don't have a key we cant refresh
        if (! isset($this->$keyName))
        {
            return;
        }

        $dbItem = self::getOneByKey($this->getKey());

        //Check if we are already the same
        if ($dbItem == $this)
        {
            return;
        }

        //Get values from DB & check if they match. Update if needed
        foreach (get_object_vars($dbItem) as $var => $val)
        {
            if ($this->$var !== $val)
            {
                $this->$var = $val;
            }
        }
    }


    /**
     * Save the current item.
     *
     * @return bool
     */
    public function save(): bool
    {
        if ($this->exists())
        {
            return $this->update();
        }

        return $this->insert();
    }


    /**
     * @return bool
     */
    private function insert(): bool
    {
        $columns = [];
        $values = $this->getValues();

        foreach ($values as $key => $value)
        {
            $columns[] = '`' . $key . '`';

            //Update the values
            unset($values[$key]);
            $values[':' . $key] = $value;
        }

        $sql = 'INSERT INTO `' . $this->getTableName() . '` (' . implode(',', $columns) . ') VALUES (' . implode(',', array_keys($values)) . ');';
        $query = DB::getConnection()->prepare($sql);
        $res = $query->execute($values);

        //If this was inserted successfully update the id
        if ($res && $lastInsertId = DB::getConnection()->lastInsertId())
        {
            $this->setKey($lastInsertId);
        }

        return $res;
    }

    /**
     * @return bool
     */
    private function update(): bool
    {
        $values = $this->getValues();
        $bindings = [];

        foreach ($values as $key => $value)
        {
            $bindings[] = '`' . $key . '`=:' . $key;

            unset($values[$key]);
            $values[':' . $key] = $value;
        }

        $keyParam = ':' . $this->getKeyName();
        $values[$keyParam] = $this->getKey();

        $sql = 'UPDATE `' . $this->getTableName() . '` SET ' . implode(',', $bindings) . ' WHERE `' . $this->getKeyName() . '` = ' . $keyParam . ';';
        $query = DB::getConnection()->prepare($sql);

        return $query->execute($values);
    }

    /**
     * Delete a model from the DB
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (! $this->exists())
        {
            return false;
        }

        $sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE ' . $this->getKeyName() . ' = :key';
        $query = DB::getConnection()->prepare($sql);

        return $query->execute(['key' => $this->getKey()]);
    }

    /**
     * Load the specified belongsTo relation model.
     *
     * @param $class
     * @param $foreignKey
     * @return LightModel
     * @throws Exception
     */
    protected function belongsTo($class, $foreignKey): ?LightModel
    {
        $identifier = implode('_', [$class, $foreignKey]);

        if (! isset($this->_belongsTo[$identifier]))
        {
            if (! $this->getTable()->hasColumn($foreignKey))
            {
                throw new ColumnMissingException($this->getTableName() . ' does not have column: ' . $foreignKey);
            }

            $this->_belongsTo[$identifier] = $class::getOneByKey($this->$foreignKey);
        }

        return $this->_belongsTo[$identifier];
    }

    /**
     * @param $class
     * @param $foreignKey
     * @return array
     * @throws Exception
     */
    protected function hasMany($class, $foreignKey): array
    {
        /* @var $class LightModel */
        $class = new $class;

        if (! $class->getTable()->hasColumn($foreignKey))
        {
            throw new ColumnMissingException($class->getTableName() . ' does not have column: ' . $foreignKey);
        }

        return $class::getItems([$foreignKey => $this->getKey()]);
    }

    /**
     * @return bool
     */
    public function preInsert(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function postInsert(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function postUpdate(): bool
    {
        return true;
    }
}