<p align="center"><b>LightModel</b></p>

<p align="center">
  <img src='https://coveralls.io/repos/github/mattvb91/LightModel/badge.svg?branch=master'/>
  <img src="https://travis-ci.org/mattvb91/LightModel.svg?branch=master">
  <img class="latest_stable_version_img" src="https://poser.pugx.org/mattvb91/lightmodel/v/stable">
  <img class="total_img" src="https://poser.pugx.org/mattvb91/lightmodel/downloads">
  <img class="latest_unstable_version_img" src="https://poser.pugx.org/mattvb91/lightmodel/v/unstable">
  <img class="license_img" src="https://poser.pugx.org/mattvb91/lightmodel/license">
</p>

## What is LightModel?

The LightModel ORM is test project to build an ActiveRecord 
style implementation from scratch.

Using this in a live project is not recommended. Please look at Eloquent or Zend Model for 
a robust and highly tested solution.

## Usage

To initialize LightModel you need to call ```LightModel::init();``` and pass your instance
of PDO to it.

```php
$pdo = new PDO(/* Your PDO params*/);
LightModel::init($pdo);
     
```

You can also pass in an optional array for further configuration. For example:

```php
$options = [
    LightModel::LightModel::OPTIONS_TYPECAST,
];

LightModel::init($pdo, $options);
```
Currently the typecast option is the only option available. If used this will typecast Integer columns
defined in your MySQL database to be an integer attribute on your model.


To get started with a model, your class needs to extend ```mattvb91\LightModel\LightModel```

### Creating a Model

```php
namespace Project;

use mattvb91\LightModel\LightModel;

class User extends LightModel
{

    //
}

```

You will need to implement the ```getValues()``` function in which you define a key value 
array for the values associated with your columns.

For example a basic ```User``` table with with the following structure could be represented
like this:

```mysql

| id (int) | username (varchar) |
```

```php
namespace Project;

use mattvb91\LightModel\LightModel;

class User extends LightModel
{
    public $username;
    
    public function getValues()
    {
        return [
            'username' => $this->username,
        ];
    }
}

```

You do not need to manually bind the primary key column if it is set up as an auto increment
value in your DB. 

You can of course use your normal getters to access your column values too inside the 
```getValues()``` method as long as you bind it to your correct column.

To create a new row on your user table you would use the following:

```php
$user = new User();
$user->username = 'Name';
$user->save();
```

To override the table name or table key simply implement the following in your class:

```php
    protected $tableName = 'new_name';
    protected $key = 'new_key';
```

### Fetch a row by key

To fetch a row by its key use the static ```Class::getOneByKey($key)``` on the class you want to fetch.
For example:

```php
$user = User::getOneByKey(1);
//$user now loaded with values from DB
```

### Check a model exists

To check if a model actually exists in your database you can use the ```exists()``` method.

```php
$user->exists(); //Returns true or false
```

### Refresh a model

You may run into situations where you need to fetch the latest version of your row again.
Use the ```refresh()``` method to update your current model. 

Keep in mind this will set your model back to whats currently in your DB.

```php
$user->refresh();
```

### Delete a model

To delete a model simply call the ```delete()``` method:

```php
$user->delete();
```

## Relationships

### BelongsTo

To define a Belongs to relationship use the ```belongsTo($class, $foreignKey)``` method in your model.
For example if our User is associated with a Department you could do the following
inside your User class.

Once a relationship has been queried once any further accesses to not hit the database again.

```php

public function department() 
{
    return $this->belongsTo(Department::class, 'department_id');
    //returns a loaded Department::class instance
}

```

## Fetching multiple rows

To fetch multiple rows simply use the ```static::getItems()``` method.

```php
$users = User::getItems();
```

## Filtering

You can also filter data sets by passing an optional array int the ```static::getItems()```
method. You must pass the correct table column name.

```php
$filter = [
    'username' => 'joe'
];

$allJoeUsers = User::getItems($filter);

```

Optionally you can also pass in the operator you want to perform. 
The order MUST be Table_Column => ['Operator', 'Value']
```php
$filter = [
    'username' => ['>=', 'joe']
];

$allJoeUsers = User::getItems($filter);

```

To set the order or limit the results returned you can make use of the
```LightModel::FILTER_ORDER``` and ```LightModel::FILTER_LIMIT``` constants
and pass them in your options array:

```php
$filter = [
    LightModel::FILTER_ORDER => 'id DESC',
    LightModel::FILTER_LIMIT => 100;
];

$filteredUsers = User::getItems($filter);
```

## Fetching keys

You may sometimes run into situations where performing a ```static::getItems()``` brings back
too large of a resultset. You can instead perform the same filters using ```static::getKeys()``` which
instead of returning an array of fully loaded Models it will now only return the unique column ID's that fit your
filtered criteria. You can then use that ID to individually load the full model manually:

```php

$filter = [
    LightModel::FILTER_ORDER => 'id DESC',
];

$userKeys = User::getKeys($filter);
//We now have an array consisting of all the primary keys that
//match our criteria

foreach($userKeys as $primaryKey) 
{
    //Load the full individual record for further processing
    $user = User::getOneByKey($primaryKey);
}
```
