<?php

namespace Ifresh\FilemakerModel\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeFilemakerModelCommand extends GeneratorCommand
{
    public $signature = 'filemaker:model {name}';

    public $description = 'Create a filemaker model wrapper';

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/filemakermodel.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Filemaker';
    }
}
