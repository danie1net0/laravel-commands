<?php

namespace Ddr\LaravelCommands\Console\Commands\Make\LivewireCrud;

use Illuminate\Support\Facades\{File, Validator};
use Ddr\LaravelCommands\Console\Commands\Traits\{CreateFiles, InputData};
use Ddr\LaravelCommands\DTOs\LivewireCrudData;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MakeListCommand extends Command
{
    use CreateFiles;
    use InputData;

    protected $signature = <<<SIGNATURE
        make:livewire-crud:list
        {name : The component name}
        {--a|authorize : Add authorizarion verification}
        {--d|data : Add load data by model}
        {--m|model= : Specifies the model used in the action}
        {--w|without-test : Create an action without tests}
    SIGNATURE;

    public function handle(): int
    {
        $componentData = new LivewireCrudData();

        if (! $this->validateParams()) {
            return Command::FAILURE;
        }

        $this->inputName($componentData)
            ->inputModel($componentData)
            ->inputCreateTest($componentData)
            ->createFiles($componentData);

        return Command::SUCCESS;
    }

    protected function validateParams(): bool
    {
        $data = [
            'name' => $this->argument('name'),
            'authorize' => $this->option('authorize'),
            'data' => $this->option('data'),
            'model' => $this->option('model'),
            'without-test' => $this->option('without-test'),
        ];

        $rules = [
            'name' => ['required', 'string'],
            'authorize' => ['nullable', 'bool'],
            'data' => ['nullable', 'bool'],
            'model' => [Rule::requiredIf(fn () => $this->option('data'))],
            'without-test' => ['nullable', 'bool'],
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

    protected function classStub(): string
    {
        $stub = match (true) {
            $this->option('authorize') && $this->option('data') => 'list-with-authorize-and-data.class.stub',
            $this->option('authorize') => 'list-with-authorize.class.stub',
            $this->option('data') => 'list-with-data.class.stub',
            default => 'list.class.stub',
        };

        return File::get(__DIR__ . '/../../../../../resources/views/stubs/livewire-crud/list/' . $stub);
    }

    protected function viewStub(): string
    {
        $stub = 'list.view.stub';

        return File::get(__DIR__ . '/../../../../../resources/views/stubs/livewire-crud/list/' . $stub);
    }

    protected function testStub(): string
    {
        $stub = match (true) {
            $this->option('authorize') && $this->option('data') => 'list-with-authorize-and-data.test.stub',
            $this->option('authorize') => 'list-with-authorize.test.stub',
            $this->option('data') => 'list-with-data.test.stub',
            default => 'list.test.stub',
        };

        return File::get(__DIR__ . '/../../../../../resources/views/stubs/livewire-crud/list/' . $stub);
    }

    protected function classContents(LivewireCrudData $componentData): string
    {
        $search = [
            '{{ namespace }}',
            '{{ modelNamespace }}',
            '{{ component }}',
            '{{ view }}',
            '{{ pluralResourceName }}',
            '{{ modelName }}',
        ];

        $replace = [
            $this->classNamespace($componentData),
            $componentData->modelNamespace,
            $this->className($componentData),
            $this->viewName($componentData),
            mb_strtolower($componentData->pluralResourceName),
            $componentData->modelName,
        ];

        return str_replace($search, $replace, $this->classStub());
    }

    protected function viewContents(): string
    {
        return $this->viewStub();
    }

    protected function testContents(LivewireCrudData $componentData): string
    {
        $search = [
            '{{ componentNamespace }}',
            '{{ modelNamespace }}',
            '{{ groups }}',
            '{{ component }}',
            '{{ pluralResourceName }}',
            '{{ modelName }}',
        ];

        $namespace = config('livewire.class_namespace');

        $namespaceReplace = [
            "{$namespace}\\Pages\\Dashboard\\",
            "{$namespace}\\",
            "{$namespace}\\Pages\\Dashboard",
            $namespace,
        ];

        $groups = str($this->classNamespace($componentData))
            ->replace($namespaceReplace, '')
            ->explode('\\')
            ->map([Str::class, 'kebab'])
            ->join("', '");

        $replace = [
            $this->classNamespace($componentData) . '\\' . $componentData->componentName,
            $componentData->modelNamespace,
            $groups ? "'{$groups}', " : '',
            $this->className($componentData),
            mb_strtolower($componentData->pluralResourceName),
            $componentData->modelName,
        ];

        return str_replace($search, $replace, $this->testStub());
    }
}
