<?php

namespace Protoqol\Quasi\Commands;

use Illuminate\Console\GeneratorCommand;

class CreateQuasiResourceCommand extends GeneratorCommand
{
    protected $name = 'make:qresource {name}';

    protected $description = 'Create a new resource with the keys preset to it\'s related table.';

    protected $type = 'Foo';

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/stubs/foo.php.stub';
    }

    /**
     * @param $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace ;
    }

    /**
     * @return bool|void|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        parent::handle();
    }
}
