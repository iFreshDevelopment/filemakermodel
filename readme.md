# Filemaker Model Wrapper for Laravel

## Installation
Install the package using composer
```
composer require ifresh/filemakermodel
```
After installation publish the config file using
```
php artisan vendor:publish --provider='Ifresh\FilemakerModel\FilemakerModelServiceProvider'
```
## The model
Create a new FilemakerModel using the following command. Mind that the Modelname argument will be the classname of the generated model. You are encouraged to use the Laravel naming conventions.
```
php artisan filemaker:model Modelname
```

Set the layout name in the generated model file
```
protected $layout = 'filemaker_layout_name'
```

## Getting your data
Get your records
```
App\Filemaker\Modelname::all();
```
