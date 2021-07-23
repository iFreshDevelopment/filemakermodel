<?php

namespace Ifresh\FilemakerModel;

use Illuminate\Support\ServiceProvider;
use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI;
use Ifresh\FilemakerModel\Commands\MakeFilemakerModelCommand;

class FilemakerModelServiceProvider extends ServiceProvider
{


    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeFilemakerModelCommand::class,
            ]);
        }
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
