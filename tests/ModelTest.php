<?php

namespace mattvb91\LightModel\Tests;

use mattvb91\LightModel\DB\DB;
use mattvb91\LightModel\Exceptions\TableColumnMissing;
use mattvb91\LightModel\LightModel;
use mattvb91\LightModel\Tests\TestModels\Book;
use mattvb91\LightModel\Tests\TestModels\Event;
use mattvb91\LightModel\Tests\TestModels\User;
use mattvb91\LightModel\Tests\TestModels\UserTableName;
use PDO;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../vendor/autoload.php');

class ModelTest extends TestCase
{

    private static $pdo;

    protected function setUp()
    {
        parent::setUp();
        self::$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');
    }

    public function testA()
    {
        $this->expectExceptionMessage('LightModel::init() not called');
        DB::getConnection();
    }

    /**
     * Make sure the connection can be created and is live
     */
    public function testB()
    {
        LightModel::init(self::$pdo);
        DB::getConnection()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        self::assertNotNull(DB::getConnection());
        self::assertNotFalse(DB::getConnection());
    }

    /**
     * Test the class name is used when $tableName is not overriden
     */
    public function testClassNameUsedForTableName()
    {
        $userTableSet = new UserTableName();
        $this->assertEquals('UserTableName', $userTableSet->getTableName());
    }

    /**
     * Test the overriden $tableName is used
     */
    public function testTableNameUsed()
    {
        $user = new User();
        $event = new Event();

        $this->assertEquals('user', $user->getTableName());
        $this->assertEquals('event', $event->getTableName());
    }

    /**
     * Test we can get records based on their Primary Keys
     */
    public function testGetByKey()
    {
        $user = User::getOneByKey(1);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->getKey(), 1);
        $this->assertTrue($user->exists());

        $event = Event::getOneByKey('test_event');
        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals($event->getKey(), 'test_event');
        $this->assertTrue($event->exists());
    }

    /**
     * Test we can retrieve items
     */
    public function testGetItems()
    {
        $items = User::getItems();
        $this->assertEquals(count($items), User::count());

        $events = Event::getItems();
        $this->assertEquals(count($events), Event::count());
    }

    /**
     * Test the exists() method works correctly
     */
    public function testExists()
    {
        $user = User::getOneByKey(1);
        $this->assertTrue($user->exists());

        $user = new User();
        $this->assertFalse($user->exists());

        $user = new User();
        $user->setKey(1);
        $this->assertTrue($user->exists());

        $event = Event::getOneByKey('test_event');
        $this->assertTrue($event->exists());

        $event = new Event();
        $this->assertFalse($event->exists());
    }

    /**
     * Test refreshing a row
     */
    public function testRefresh()
    {
        $user = User::getOneByKey(1);

        $actual = $user->username;

        $user->username = 'Test';
        $user->refresh();
        $this->assertEquals($user->username, $actual);

        $event = Event::getOneByKey('test_event');
        $actual = $event->getValues();

        $event->name = 'Updating to something else';
        $event->date = null;
        $event->description = 'New description';
        $event->refresh();

        $this->assertEquals($actual, $event->getValues());
    }

    /**
     * Test the save method
     */
    public function testSave()
    {
        $user = new User();
        $user->username = time();

        //Test insert
        $this->assertTrue($user->save());
        $this->assertTrue($user->exists());
        $this->assertNotNull($user->getKey());

        $key = $user->getKey();

        //Test update
        $updatedName = time() . ' updated';

        $user->username = $updatedName;
        $this->assertTrue($user->save());

        $user->refresh();

        $this->assertEquals($user, User::getOneByKey($key));

        $event = new Event();
        $event->setKey('new key');
        $event->description = 'New description';
        $event->name = 'New Name';
        $event->date = '2010-10-20 00:01:01';
        $this->assertTrue($event->save());

        $values = $event->getValues();

        $eventDB = Event::getOneByKey('new key');
        $this->assertEquals($event, $eventDB);
        $this->assertEquals($values, $eventDB->getValues());
    }

    /**
     * Test deleting a model
     */
    public function testDelete()
    {
        $user = User::getOneByKey(1);
        $this->assertTrue($user->delete());
        $this->assertFalse($user->exists());
        $this->assertNull(User::getOneByKey(1));

        $eventDB = Event::getOneByKey('new key');
        $this->assertTrue($eventDB->delete());
        $this->assertFalse($eventDB->exists());
        $this->assertNull(User::getOneByKey('new key'));

        $newEvent = new Event();
        $this->assertFalse($newEvent->delete());
    }

    /**
     * Test typecasting works correctly
     */
    public function testTypeCast()
    {
        $user = new User();
        $user->username = uniqid('username');
        $user->save();

        $user = User::getOneByKey($user->getKey());
        $type = gettype($user->getKey());
        $this->assertEquals('string', $type);

        //Init LightModel with TypeCast option
        LightModel::init(self::$pdo, [LightModel::OPTIONS_TYPECAST]);

        $user = User::getOneByKey($user->getKey());
        $type = gettype($user->getKey());
        $this->assertEquals('integer', $type);

        $user->refresh();
        $type = gettype($user->getKey());
        $this->assertEquals('integer', $type);
    }


    /**
     * Test foreign relationships.
     */
    public function testBelongsToRelationship()
    {

        $user = new User();
        $user->username = uniqid('username');
        $user->save();

        $book = new Book();
        $book->name = 'New book';
        $book->user_id = $user->getKey();
        $book->save();

        $this->assertInstanceOf(User::class, $book->user());

        $this->expectException(TableColumnMissing::class);
        $this->expectExceptionMessage('books does not have column: wrong');
        $book->wrongForeignKey();
    }


    /**
     * Test Filters
     */
    public function testFilters()
    {
        LightModel::init(self::$pdo, [LightModel::OPTIONS_TYPECAST]);

        for ($i = 0; $i <= 5; $i++)
        {
            $user = new User();
            $user->username = uniqid('username');
            $user->save();
        }

        $user = new User();
        $user->username = 123;
        $user->save();

        $user = new User();
        $user->username = 200;
        $user->save();

        $user = new User();
        $user->username = 300;
        $user->save();

        $users = User::getItems(['username' => 123]);
        $this->assertEquals(count($users), User::count(['username' => 123]));

        $this->assertEquals(1, count(User::getItems(['username' => 123])));

        $this->assertGreaterThan(1, count(User::getItems(['username' => ['>=', 12]])));
    }

    public function testHasMany()
    {
        LightModel::init(self::$pdo, [LightModel::OPTIONS_TYPECAST]);

        $user = new User();
        $user->username = uniqid('username');
        $user->save();

        $book = new Book();
        $book->name = 'New book';
        $book->user_id = $user->getKey();
        $book->save();

        $book2 = new Book();
        $book2->name = 'New book2';
        $book2->user_id = $user->getKey();
        $book2->save();

        $this->assertNotEmpty($user->books());
        $this->assertEquals(2, count($user->books()));
    }
}