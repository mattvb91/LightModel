<?php

namespace mattvb91\LightModel\DB;

use Exception;
use mattvb91\LightModel\LightModel;
use PDO;

/**
 * Class DB
 * @package mattvb91\LightModel\DB
 */
abstract class DB
{

    /**
     * @var PDO
     */
    private static $connection;

    /**
     * List of all the tables available.
     *
     * @var array
     */
    private static $tables = [];

    /**
     * @return PDO
     * @throws Exception
     */
    public static function getConnection(): PDO
    {
        if (! isset(self::$connection))
        {
            throw new Exception('LightModel::init() not called');
        }

        return self::$connection;
    }

    /**
     * @param PDO $pdo
     */
    public static function init(PDO $pdo)
    {
        self::$connection = $pdo;
    }

    /**
     * We only want to ever load a table once so we check if we
     * already have an instantiated instance of it before loading.
     *
     * @param LightModel $model
     * @return Table
     */
    public static function getModelTable(LightModel $model): Table
    {
        if (! isset(self::$tables[$model->getTableName()]))
        {
            self::$tables[$model->getTableName()] = new Table($model->getTableName());
        }

        return self::$tables[$model->getTableName()];
    }
}