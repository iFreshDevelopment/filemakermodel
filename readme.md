###Filemaker Model Wrapper for Laravel
Create a new FilemakerModel
```
php artisan filemaker:model Modelname
```

Set the layout name in the generated model file
```
protected $layout = 'filemaker_layout_name
```

Get your records
```
    App\Filemaker\Modelname::all();
```
