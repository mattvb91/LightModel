<p align="center"><b>LightModel</b></p>

<p align="center">
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
    $username;
    
    public function getValues()
    {
        return [
            'username' => $this->username,
        ];
    }
}

```

You do not need to manually bind the primary key column as it is done automatically. 

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