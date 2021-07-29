<?php

namespace Ifresh\FilemakerModel\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeFilemakerModelCommand extends GeneratorCommand
{
    public $signature = 'make:filemaker {name} {--f|full}';

    public $description = 'Create a filemaker model wrapper';

    protected function getStub(): string
    {
        $stubFilename = $this->option('full')
            ? 'filemakermodel.stub'
            : 'filemakermodel-basic.stub';

        return __DIR__ . '/stubs/' . $stubFilename;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Filemaker';
    }
}
