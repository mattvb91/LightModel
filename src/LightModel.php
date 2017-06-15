<?php


namespace mattvb91\LightModel;

use Exception;
use PDO;

/**
 * Class LightModel
 * @package mattvb91\LightModel
 */
abstract class LightModel
{

    /**
     * @var $connection PDO
     */
    private static $connection;

    protected $keyName = 'id';

    protected $key;

    /**
     * The associated table name.
     * If not set the class name is used.
     *
     * @var String
     */
    protected $tableName;

    /**
     * This is used for proper typecasting of columns.
     *
     * @var array
     */
    private static $tableColumns = [];

    /**
     * @var array
     */
    private static $initOptions = [];

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
            $model->describeTable();

            foreach (self::$tableColumns[$model->getTableName()] as $column => $type)
            {
                if (in_array($column, get_object_vars($model)))
                {
                    if (strpos($type, 'int') !== false)
                    {
                        settype($model->$column, 'int');
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
    abstract public function getValues();


    //Typecast model attributes to mysql data types
    const OPTIONS_TYPECAST = 1;

    /**
     * @param PDO $pdo
     * @param array $options
     */
    public static function init(PDO $pdo, $options = [])
    {
        self::$connection = $pdo;
        self::$initOptions = $options;
    }

    /**
     * @return PDO
     * @throws Exception
     */
    public static function getConnection()
    {
        if (! isset(self::$connection))
        {
            throw new Exception('LightModel::init() not called');
        }

        return self::$connection;
    }

    /**
     * Check if tableName has been set or return based on class;
     * @return String
     */
    public function getTableName()
    {
        if ($this->tableName === null)
        {
            $this->tableName = (new \ReflectionClass($this))->getShortName();
        }

        return $this->tableName;
    }

    /**
     * @return mixed
     */
    public function getKeyName()
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
    public static function getOneByKey($key)
    {
        /* @var $class LightModel */
        $className = get_called_class();
        $class = new $className;
        $tableKey = $class->getKeyName();

        $sql = 'SELECT * FROM ' . $class->getTableName() . ' WHERE ' . $tableKey . ' = :key';
        $query = self::getConnection()->prepare($sql);
        $query->execute(['key' => $key]);

        if ($res = $query->fetchObject($className))
        {
            return self::typeCastModel($res);
        }

        return null;
    }

    /**
     * Get all items based on filter
     *
     * @param array $filter
     * @return array
     */
    public static function getItems($filter = [])
    {
        $res = [];

        /* @var $class LightModel */
        $className = get_called_class();
        $class = new $className;

        $sql = 'SELECT * FROM ' . $class->getTableName();
        $query = self::getConnection()->query($sql);

        foreach ($query->fetchAll(PDO::FETCH_CLASS, $className) as $item)
        {
            $res[] = self::typeCastModel($item);
        }

        return $res;
    }


    /**
     * Count items based on filter
     *
     * @param array $filter
     * @return int
     */
    public static function count($filter = [])
    {
        /* @var $class LightModel */
        $className = get_called_class();
        $class = new $className;
        $tableKey = $class->getKeyName();

        $sql = 'SELECT COUNT(' . $tableKey . ') FROM ' . $class->getTableName();
        $query = self::getConnection()->query($sql);

        return (int) $query->fetchColumn(0);
    }


    /**
     * Check does the current item exist.
     *
     * @return bool
     */
    public function exists()
    {
        $tableKey = $this->getKeyName();

        //If key isn't set we cant associate this record with DB
        if (! isset($this->$tableKey))
        {
            return false;
        }

        $sql = 'SELECT EXISTS(SELECT ' . $tableKey . ' FROM ' . $this->getTableName() . ' WHERE ' . $this->getKeyName() . ' = :key LIMIT 1)';
        $query = self::getConnection()->prepare($sql);
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
    public function save()
    {
        $values = $this->getValues();
        $keys = array_keys($values);

        if ($this->exists())
        {
            $setString = '';
            foreach ($values as $key => $value)
            {
                $setString .= '`' . $key . '`=:' . $key;

                if (end($keys) !== $key)
                {
                    $setString .= ',';
                }

                unset($values[$key]);
                $values[':' . $key] = $value;
            }

            $keyParam = ':' . $this->getKeyName();
            $values[$keyParam] = $this->getKey();

            $sql = 'UPDATE `' . $this->getTableName() . '` SET ' . $setString . ' WHERE `' . $this->getKeyName() . '` = ' . $keyParam . ';';
        } else
        {
            $bindings = [
                'columns' => '',
                'values'  => '',
            ];

            foreach ($values as $key => $value)
            {
                $bindings['columns'] .= '`' . $key . '`';
                $bindings['values'] .= ':' . $key . '';

                if (end($keys) !== $key)
                {
                    $bindings['columns'] .= ',';
                    $bindings['values'] .= ',';
                }

                //Update the values
                unset($values[$key]);
                $values[':' . $key] = $value;
            }

            $sql = 'INSERT INTO `' . $this->getTableName() . '` (' . $bindings['columns'] . ') VALUES (' . $bindings['values'] . ');';
        }

        $query = self::getConnection()->prepare($sql);
        $res = $query->execute($values);

        //If this was inserted successfully update the id
        if (! $this->exists() && $res)
        {
            $this->setKey(self::getConnection()->lastInsertId());
        }

        return $res;
    }

    /**
     * Delete a model from the DB
     *
     * @return bool
     */
    public function delete()
    {
        if (! $this->exists())
        {
            return false;
        }

        $sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE ' . $this->getKeyName() . ' = :key';
        $query = self::getConnection()->prepare($sql);

        return $query->execute(['key' => $this->getKey()]);
    }

    /**
     * Describe the associated table columns and set up our
     * $tableColumns
     */
    private function describeTable()
    {
        if (! isset(self::$tableColumns[$this->getTableName()]))
        {
            $sql = 'DESCRIBE ' . $this->getTableName();
            $query = self::getConnection()->prepare($sql);
            $query->execute();

            foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $values)
            {
                self::$tableColumns[$this->getTableName()][$values['Field']] = $values['Type'];
            }
        }
    }

    private $_belongsTo = [];
    private $_hasMany = [];

    /**
     * Load the specified belongsTo relation model.
     *
     * @param $class
     * @param $foreignKey
     * @return LightModel
     * @throws Exception
     */
    protected function belongsTo($class, $foreignKey)
    {
        if (! property_exists($this, $foreignKey))
        {
            throw new Exception($class . ' does not have attribute: ' . $foreignKey);
        }

        if (! isset($this->_belongsTo[$class]))
        {
            $this->_belongsTo[$class] = $class::getOneByKey($this->$foreignKey);
        }

        return $this->_belongsTo[$class];
    }
}