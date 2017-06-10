<?php

namespace mattvb91\LightModel\Tests;

use mattvb91\LightModel\LightModel;
use mattvb91\LightModel\Tests\TestModels\User;
use mattvb91\LightModel\Tests\TestModels\UserTableNameSet;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../vendor/autoload.php');

class ModelTest extends TestCase
{

    public function testA()
    {
        $this->expectExceptionMessage('LightModel::init() not called');
        LightModel::getConnection();
    }

    /**
     * Make sure the connection can be created and is live
     */
    public function testB()
    {
        LightModel::init(new \PDO('mysql:host=localhost;dbname=test', 'root', 'root'));
        self::assertNotNull(LightModel::getConnection());
    }

    public function testClassNameUsedForTableName()
    {
        $user = new User();
        $userTableSet = new UserTableNameSet();

        $this->assertEquals('user', $user->getTableName());
        $this->assertEquals('User', $userTableSet->getTableName());
    }

    public function testGetByKey()
    {
        $user = User::getOneByKey(1);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->getKey(), 1);
    }

    public function testGetItems()
    {
        $items = User::getItems();

        $this->assertEquals(count($items), User::count());
    }

    public function testExists()
    {
        $user = User::getOneByKey(1);

        $this->assertTrue($user->exists());

        $user = new User();
        $this->assertFalse($user->exists());

        $user = new User();
        $user->setKey(1);
        $this->assertTrue($user->exists());
    }

    public function testRefresh()
    {
        $user = User::getOneByKey(1);

        $actual = $user->username;

        $user->username = 'Test';

        $user->refresh();
        $this->assertEquals($user->username, $actual);
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
    }
}