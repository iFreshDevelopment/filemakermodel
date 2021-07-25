# Filemaker Model Wrapper for Laravel

## Installation
Install the package using composer
```php
composer require ifresh/filemaker-model
```
After installation publish the config file using
```php
php artisan vendor:publish --provider='Ifresh\FilemakerModel\FilemakerModelServiceProvider'
```
## The model
Create a new FilemakerModel using the following command. Mind that the Modelname argument will be the classname of the generated model. You are encouraged to use the Laravel naming conventions.
```php
php artisan filemaker:model Modelname
```

Set the layout name in the generated model file
```php
protected $layout = 'filemaker_layout_name'
```

## Getting your data
Get your records
```php
App\Filemaker\Modelname::all();
// returns an eloquent collection with all models
```

If you know the Filemaker internal recordId, you can fetch records with the `find` method.
```php
App\Filemaker\Modelname::find(234);
// returns a single model instance
```

## Create a record
You can create records with ease by using an eloquent like method:
```php
$recordId = App\Filemaker\Modelname::create([
    'key1' => 'value',
    'key2' => 'value2'
]);
// returns the given recordId
```
