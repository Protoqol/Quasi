<?php

namespace Protoqol\Quasi\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class QuasiResource extends GeneratorCommand
{
    protected $signature = 'make:qresource {name} {table?}';

    protected $type = 'Resource';

    protected $description = 'Create a new resource with the keys preset to it\'s related table.';

    protected $tableName;

    /**
     * Handler.
     *
     * @return bool|void|null
     * @throws FileNotFoundException
     */
    public function handle()
    {
        if ($this->input->getArgument('table') !== null) {
            $this->tableName = $this->input->getArgument('table');
        } else {
            $guess = Str::snake(Str::plural(str_replace('Resource', '', $this->getNameInput())));

            try {
                DB::table($guess)->exists();
            } catch (\Exception $e) {
                $this->error("Table '$guess' does not exist. Try defining the table with the secondary parameter.");
                return 0;
            }

            $this->tableName = $guess;
        }

        if (!$this->alreadyExists($this->getNameInput())) {
            $this->info("Generating '{$this->getNameInput()}' based on '{$this->tableName}' table.");
        }

        parent::handle();
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name): string
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        $stub = $this->replaceKeys($stub);

        return str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);
    }

    /**
     * Replace keys.
     *
     * @param  string  $stub
     *
     * @return string
     */
    protected function replaceKeys(string &$stub): string
    {
        $search = '{{Keys}}';

        $columns = Schema::getColumnListing($this->tableName);

        $string = '';

        foreach ($columns as $column) {
            if (config('quasi.exclude', false)) {
                if (in_array($column, config('quasi.exclude'), true)) {
                    continue;
                }
            }

            $string .= "'$column' => \$this->$column,                
            ";
        }

        return str_replace($search, $string, $stub);
    }

    /**
     * Get stub file.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../Stubs/QResource.stub';
    }

    /**
     * Get root namespace.
     *
     * @param $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Http\Resources';
    }
}
