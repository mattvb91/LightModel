<p align="center"><b>LightModel</b></p>

<p align="center">
  <img class="latest_stable_version_img" src="https://poser.pugx.org/mattvb91/lightmodel/v/stable">
  <img class="total_img" src="https://poser.pugx.org/mattvb91/lightmodel/downloads">
  <img class="latest_unstable_version_img" src="https://poser.pugx.org/mattvb91/lightmodel/v/unstable">
  <img class="license_img" src="https://poser.pugx.org/mattvb91/lightmodel/license">
</p>

##What is LightModel?

The LightModel ORM is test project to build an ActiveRecord 
style implementation from scratch.

Using this in a live project is not recommended. Please look at Eloquent or Zend Model for 
a robust and highly tested solution.

##Usage

To get started, your model class needs to extend ```mattvb91\LightModel\LightModel```

### Creating a Model

```php
namespace Project;

use mattvb91\LightModel\LightModel;

class User extends LightModel
{

}

```