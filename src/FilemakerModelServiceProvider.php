<?php

namespace Ifresh\FilemakerModel;

use Ifresh\FilemakerModel\Commands\MakeFilemakerModelCommand;
use Illuminate\Support\ServiceProvider;
use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI;

class FilemakerModelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeFilemakerModelCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/Config/filemaker.php' => config_path('filemaker.php'),
        ]);
    }

    public function register()
    {
        app()->bind('filemaker', function () {
            return new FMDataAPI(
                config('filemaker.database'),
                config('filemaker.user'),
                config('filemaker.password'),
                config('filemaker.host')
            );
        });
    }
}
