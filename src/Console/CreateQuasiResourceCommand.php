<?php

namespace Protoqol\Quasi\Console;

use Exception;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateQuasiResourceCommand extends GeneratorCommand
{
    protected $signature = 'make:qresource
                            {name : The name of the resource to generate}
                            {--table= : The name of the table to use for the resource (optional when name is clear enough)}
                            {--model= : The name of the model to use for the resource (taken $hidden into account)}
                            {--all : Generate resources for all tables in the database}
                            {--only= : Only include the specified columns (comma-separated)}
                            {--except= : Exclude the specified columns (comma-separated)}';

    protected $type = 'Resource';

    protected $description = 'Create a new resource with the keys preset to it\'s related table.';

    protected string $resourceName;

    protected string|null $tableName;

    /**
     * Handler.
     *
     * @return int
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->handleAll();
        }

        $this->resourceName = $this->argument('name');
        $this->tableName    = $this->option('table');

        if ($this->option('model')) {
            $modelClass = $this->option('model');

            if (!class_exists($modelClass)) {
                $rootNamespace = $this->laravel->getNamespace();
                $modelClass    = $rootNamespace . $modelClass;

                if (!class_exists($modelClass)) {
                    $modelClass = $rootNamespace . 'Models\\' . $this->option('model');
                }
            }

            if (class_exists($modelClass)) {
                $model           = new $modelClass;
                $this->tableName = $model->getTable();
            } else {
                $this->error("Model '{$modelClass}' does not exist.");
                return 1;
            }
        }

        if ($this->tableName === null) {
            $guess = Str::snake(Str::plural(str_replace(['Resource'], '', $this->resourceName)));

            try {
                DB::table($guess)->exists();
            } catch (Exception $e) {
                $this->error("Table '{$guess}' does not exist. Try defining the table with the --table or --model option.");
                return 1;
            }

            $this->tableName = $guess;
        }

        if (!$this->alreadyExists($this->resourceName)) {
            $this->info("Generating '{$this->resourceName}' based on '{$this->tableName}' table.");
        }

        parent::handle();

        return 0;
    }

    /**
     * Handle generation for all tables.
     *
     * @return int
     */
    protected function handleAll(): int
    {
        $tables = Schema::getTableListing();

        foreach ($tables as $table) {
            if ($table === 'migrations') {
                continue;
            }

            $name = Str::studly(Str::singular($table)) . 'Resource';

            $this->call('make:qresource', [
                'name'    => $name,
                '--table' => $table,
            ]);
        }

        return 0;
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => 'What should the resource be called? Use a clear singular name with "Resource" appended, e.g. UserResource.',
        ];
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

        $only   = $this->option('only') ? explode(',', $this->option('only')) : [];
        $except = $this->option('except') ? explode(',', $this->option('except')) : [];

        $globalExclude    = config('quasi.exclude', []);
        $sensitiveExclude = ['password', 'token', 'secret', 'remember_token'];

        foreach ($columns as $column) {
            // --only filter
            if (!empty($only) && !in_array($column, $only, true)) {
                continue;
            }

            // --except filter
            if (in_array($column, $except, true)) {
                continue;
            }

            // Global exclude config
            if (in_array($column, $globalExclude, true)) {
                continue;
            }

            // Sensitive data exclude
            if (in_array($column, $sensitiveExclude, true)) {
                continue;
            }

            if (Str::endsWith($column, '_id')) {
                $relation         = Str::beforeLast($column, '_id');
                $relationResource = Str::studly($relation) . 'Resource';
                $string           .= "'{$column}' => \$this->{$column},
            // '{$relation}' => new {$relationResource}(\$this->whenLoaded('{$relation}')),
            ";
                continue;
            }

            $string .= "'{$column}' => \$this->{$column},
            ";
        }

        return str_replace($search, $string, $stub);
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

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        $publishedPath = base_path('stubs/qresource.stub');

        if (file_exists($publishedPath)) {
            return $publishedPath;
        }

        return __DIR__ . '/../Stubs/QResource.stub';
    }
}
