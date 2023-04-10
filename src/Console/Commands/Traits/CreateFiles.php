<?php

namespace Ddr\LaravelCommands\Console\Commands\Traits;

use Ddr\LaravelCommands\DTOs\LivewireCrudData;

trait CreateFiles
{
    use CreateClass;
    use CreateTest;
    use CreateView;

    protected function createFiles(LivewireCrudData $componentData): self
    {
        $class = $this->createClass($componentData);
        $view = $this->createView($componentData);
        $test = '';

        if ($componentData->createTest) {
            $test = $this->createTest($componentData);
        }

        if ($class || $view) {
            $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙\n");

            $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->relativeClassPath($componentData)}");

            $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->relativeViewPath($componentData)}");

            if ($test) {
                $test && $this->line("<options=bold;fg=green>TEST:</>  {$this->relativeTestPath($componentData)}");
            }
        }

        return $this;
    }
}
