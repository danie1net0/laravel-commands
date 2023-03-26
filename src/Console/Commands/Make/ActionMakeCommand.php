<?php

namespace Ddr\LaravelCommands\Console\Commands\Make;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\{File, Validator};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use function str_pad;

class ActionMakeCommand extends GeneratorCommand
{
    protected $signature = <<<SIGNATURE
        make:action
        {name : The action name}
        {--create : Create a model create action }
        {--update : Create a model update action}
        {--delete : Create a model delete action}
        {--m|model= : Specifies the model used in the action}
        {--w|without-test : Create an action without tests}
        {--u|unit-test : Create an action with unit test instead of feature test (default)}
    SIGNATURE;

    protected $description = 'Create a new action';

    protected $type = 'Action';

    public function handle(): bool|null
    {
        if (! $this->validateParams()) {
            return false;
        }

        if (! $this->option('without-test')) {
            $this->createTest();
        }

        return parent::handle();
    }

    protected function getStub(): string
    {
        $stubPath = match (true) {
            $this->option('create') => 'stubs/actions/action.crud-creation.stub',
            $this->option('update') => 'stubs/actions/action.crud-editing.stub',
            $this->option('delete') => 'stubs/actions/action.crud-deleting.stub',
            default => 'stubs/actions/action.stub'
        };

        return __DIR__ . "/../../../../{$stubPath}";
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Actions';
    }

    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        $stub = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

        return $this->replaceModel($stub);
    }

    protected function replaceModel($stub): string
    {
        $modelNamespace = str($this->option('model'))->replace('/', '\\');
        $model = $modelNamespace->explode('\\')->pop();
        $instance = '$' . str($model)->camel();

        $search = [
            '{{ modelNamespace }}',
            '{{ model }}',
            '{{ instance }}',
        ];

        $replace = [
            $modelNamespace,
            $model,
            $instance,
        ];

        return str_replace($search, $replace, $stub);
    }

    private function validateParams(): bool
    {
        $data = [
            'create' => $this->option('create'),
            'update' => $this->option('update'),
            'delete' => $this->option('delete'),
            'model' => $this->option('model'),
            'without-test' => $this->option('without-test'),
            'unit-test' => $this->option('unit-test'),
        ];

        $rules = [
            'create' => ['nullable', 'bool'],
            'update' => ['nullable', 'bool'],
            'delete' => ['nullable', 'bool'],
            'model' => [Rule::requiredIf(fn () => $this->isCrudAction())],
            'without-test' => ['nullable', 'bool'],
            'unit-test' => ['nullable', 'bool'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->passes()) {
            return true;
        }

        foreach ($validator->errors()->all() as $error) {
            $this->components->error($error);
        }

        return false;
    }

    private function isCrudAction(): bool
    {
        return $this->option('create') || $this->option('update') || $this->option('delete');
    }

    private function createTest(): void
    {
        $testPath = $this->testPath();

        if (File::exists($testPath)) {
            $this->line("<options=bold,reverse;fg=red> Error</> Test class already exists \n");
            $this->alert("Test class already exists \n");

            return;
        }

        $this->ensureDirectoryExists($testPath);

        File::put($testPath, $this->replaceTest());
    }

    private function testPath(): string
    {
        $testPath = str(base_path('Tests\Feature\Actions'))
            ->replace('\\', '/')
            ->replaceFirst('T', 't');

        $baseTestPath = rtrim($testPath, DIRECTORY_SEPARATOR) . '/';

        return $baseTestPath . collect()
            ->push($this->argument('name') . 'Test.php')
            ->implode('/');
    }

    private function ensureDirectoryExists($path): void
    {
        if (! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, $recursive = true, $force = true);
        }
    }

    private function relativeTestPath(): string
    {
        return str($this->testPath())->replaceFirst(base_path() . '/', '');
    }

    private function replaceTest(): string
    {
        return match (true) {
            $this->option('create') => $this->createCreationTest(),
            $this->option('update') => $this->createEditingTest(),
            $this->option('delete') => $this->createDeletingTest(),
            default => $this->createDefaultTest(),
        };
    }

    private function createDefaultTest(): string
    {
        $testStub = File::get(__DIR__ . '/../../../../stubs/actions/tests/action.test.stub');

        $search = [
            '{{ actionNamespace }}',
            '{{ actionName }}',
        ];

        $actionNamespace = str($this->getDefaultNamespace($this->rootNamespace()))
            ->append(DIRECTORY_SEPARATOR . $this->argument('name'))
            ->replace(['\\\\', '/'], '\\');

        $action = str($actionNamespace)
            ->explode('\\')
            ->pop();

        $replace = [
            $actionNamespace,
            $action,
        ];

        return str_replace($search, $replace, $testStub);
    }

    private function createCreationTest(): string
    {
        $testStub = File::get(__DIR__ . '/../../../../stubs/actions/tests/action.crud-creation.test.stub');

        $search = [
            '{{ actionNamespace }}',
            '{{ modelNamespace }}',
            '{{ groups }}',
            '{{ modelInstance }}',
            '{{ modelName }}',
            '{{ actionName }}',
            '{{ databaseAttributes }}',
        ];

        $actionNamespace = str($this->getDefaultNamespace($this->rootNamespace()))
            ->append(DIRECTORY_SEPARATOR . $this->argument('name'))
            ->replace(['\\\\', '/'], '\\');

        $action = str($actionNamespace)
            ->explode('\\')
            ->pop();

        $modelNamespace = str($this->option('model'))
            ->replace('/', '\\');

        $model = str($modelNamespace)
            ->replace('/', '\\')
            ->explode('\\')
            ->pop();

        $modelInstance = str($model)->camel();

        $groups = str($this->argument('name'))
            ->replace("/{$action}", '')
            ->explode('/')
            ->map([Str::class, 'kebab'])
            ->join("', '");

        $databaseAttributes = collect(app($modelNamespace->value())->getFillable());

        $databaseAttributes = $databaseAttributes->reduce(
            function (string $carry, string $attribute, int $index) use ($databaseAttributes, $modelInstance) {
                $endOfLine = "\n";

                if ($index === 0) {
                    $carry .= "'id' => \${$modelInstance}->id,{$endOfLine}";
                }

                if ($index === count($databaseAttributes) - 1) {
                    $endOfLine = '';
                }

                return $carry . str_pad('', 8) . "'{$attribute}' => \${$modelInstance}Data['{$attribute}'],{$endOfLine}";
            },
            ''
        );

        $replace = [
            $actionNamespace,
            $modelNamespace,
            $groups ? "'{$groups}'" : '',
            $modelInstance,
            $model,
            $action,
            $databaseAttributes,
        ];

        return str_replace($search, $replace, $testStub);
    }

    private function createEditingTest(): string
    {
        $testStub = File::get(__DIR__ . '/../../../../stubs/actions/tests/action.crud-editing.test.stub');

        $search = [
            '{{ actionNamespace }}',
            '{{ modelNamespace }}',
            '{{ groups }}',
            '{{ modelInstance }}',
            '{{ updatedModelInstance }}',
            '{{ modelName }}',
            '{{ actionName }}',
            '{{ databaseAttributes }}',
        ];

        $actionNamespace = str($this->getDefaultNamespace($this->rootNamespace()))
            ->append(DIRECTORY_SEPARATOR . $this->argument('name'))
            ->replace(['\\\\', '/'], '\\');

        $action = str($actionNamespace)
            ->explode('\\')
            ->pop();

        $modelNamespace = str($this->option('model'))
            ->replace('/', '\\');

        $model = str($modelNamespace)
            ->replace('/', '\\')
            ->explode('\\')
            ->pop();

        $modelInstance = str($model)->camel();

        $updatedModelInstance = 'updated' . $modelInstance->ucfirst();

        $groups = str($this->argument('name'))
            ->replace("/{$action}", '')
            ->explode('/')
            ->map([Str::class, 'kebab'])
            ->join("', '");

        $databaseAttributes = collect(app($modelNamespace->value())->getFillable());

        $databaseAttributes = $databaseAttributes->reduce(
            function (string $carry, string $attribute, int $index) use ($databaseAttributes, $modelInstance, $updatedModelInstance) {
                $endOfLine = "\n";

                if ($index === 0) {
                    $carry .= "'id' => \${$modelInstance}->id,{$endOfLine}";
                }

                if ($index === count($databaseAttributes) - 1) {
                    $endOfLine = '';
                }

                return $carry . str_pad('', 8) . "'{$attribute}' => \${$updatedModelInstance}->{$attribute},{$endOfLine}";
            },
            ''
        );

        $replace = [
            $actionNamespace,
            $modelNamespace,
            $groups ? "'{$groups}'" : '',
            $modelInstance,
            $updatedModelInstance,
            $model,
            $action,
            $databaseAttributes,
        ];

        return str_replace($search, $replace, $testStub);
    }

    private function createDeletingTest(): string
    {
        $testStub = File::get(__DIR__ . '/../../../../stubs/actions/tests/action.crud-deleting.test.stub');

        $search = [
            '{{ actionNamespace }}',
            '{{ modelNamespace }}',
            '{{ groups }}',
            '{{ modelInstance }}',
            '{{ modelName }}',
            '{{ actionName }}',
            '{{ databaseAttributes }}',
        ];

        $actionNamespace = str($this->getDefaultNamespace($this->rootNamespace()))
            ->append(DIRECTORY_SEPARATOR . $this->argument('name'))
            ->replace(['\\\\', '/'], '\\');

        $action = str($actionNamespace)
            ->explode('\\')
            ->pop();

        $modelNamespace = str($this->option('model'))
            ->replace('/', '\\');

        $model = str($modelNamespace)
            ->replace('/', '\\')
            ->explode('\\')
            ->pop();

        $modelInstance = str($model)->camel();

        $groups = str($this->argument('name'))
            ->replace("/{$action}", '')
            ->explode('/')
            ->map([Str::class, 'kebab'])
            ->join("', '");

        $databaseAttributes = "'id' => \${$modelInstance}->id,";

        $replace = [
            $actionNamespace,
            $modelNamespace,
            $groups ? "'{$groups}'" : '',
            $modelInstance,
            $model,
            $action,
            $databaseAttributes,
        ];

        return str_replace($search, $replace, $testStub);
    }
}
