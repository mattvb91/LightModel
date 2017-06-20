<?php

namespace mattvb91\LightModel\Tests;

use mattvb91\LightModel\DB\DB;
use mattvb91\LightModel\DB\Table;
use mattvb91\LightModel\LightModel;
use mattvb91\LightModel\Tests\TestModels\Event;
use mattvb91\LightModel\Tests\TestModels\User;
use PDO;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../vendor/autoload.php');

class TableTest extends TestCase
{

    private static $pdo;

    protected function setUp()
    {
        parent::setUp();

        self::$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');
        LightModel::init(self::$pdo);
        DB::getConnection()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function testTable()
    {
        $table = new Table('user');

        $this->assertNotEmpty($table->getColumns());
    }

    /**
     * Test describe table works correctly
     */
    public function testDescribeTable()
    {
        LightModel::init(self::$pdo, [LightModel::OPTIONS_TYPECAST]);

        //Run getItems just to populate $tableColumns
        User::getItems();
        Event::getItems();

        $event = new Event();
        $table = $event->getTable();

        $user = new User();
        $userTable = $user->getTable();

        $this->assertNotNull($table);
        $this->assertNotNull($userTable);

        $this->assertEquals(2, count($userTable->getColumns()));
        $this->assertEquals(4, count($table->getColumns()));

        $this->assertTrue($userTable->hasColumn('username'));
        $this->assertTrue($table->hasColumn('event_id'));

    }
}