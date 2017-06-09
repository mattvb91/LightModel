<?php


namespace mattvb91\LightModel;

use PDO;

/**
 * Class LightModel
 * @package mattvb91\LightModel
 */
class LightModel
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
     * @param $pdo
     * @param array $options
     */
    public static function init($pdo, $options = [])
    {
        self::$connection = $pdo;
    }

    /**
     * @return PDO
     * @throws \Exception
     */
    public static function getConnection()
    {
        if (! isset(self::$connection))
        {
            throw new \Exception('LightModel::init() not called');
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

        $sql = 'SELECT * FROM ' . $class->getTableName() . ' WHERE ' . $tableKey . ' = ' . $key;
        $query = self::getConnection()->query($sql);

        $query->execute();

        if ($res = $query->fetchObject($className))
        {
            return $res;
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
            $res[] = $item;
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
        /* @var $class LightModel */
        $className = get_called_class();
        $class = new $className;
        $tableKey = $class->getKeyName();

        //If key isn't set we cant associate this record with DB
        if (! isset($this->$tableKey))
        {
            return false;
        }

        $sql = 'SELECT EXISTS(SELECT * FROM user WHERE ' . $tableKey . ' = ' . $this->getKey() . ' LIMIT 1)';
        $query = self::getConnection()->query($sql);

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
}