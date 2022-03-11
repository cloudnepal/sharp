<?php

namespace Code16\Sharp\EntityList\Commands\Wizards;

use Code16\Sharp\Exceptions\SharpMethodNotImplementedException;
use Illuminate\Support\Str;

trait IsInstanceWizardCommand
{
    public function execute(mixed $instanceId, array $data = []): array
    {
        if (! $step = $this->extractStepFromRequest()) {
            return $this->executeFirstStep($instanceId, $data);
        }

        $this->getWizardContext()->setCurrentStep($step);

        $methodName = 'executeStep'.Str::ucfirst(Str::camel($step));

        return method_exists($this, $methodName)
            ? $this->$methodName($instanceId, $data)
            : $this->executeStep($step, $instanceId, $data);
    }

    abstract protected function executeFirstStep(mixed $instanceId, array $data): array;

    public function executeStep(string $step, mixed $instanceId, array $data = []): array
    {
        // You can either implement this method and test $step (quick for small commands)
        // or leave this and implement for each step executeStepXXX
        // where XXX is the camel cased name of your step
        throw new SharpMethodNotImplementedException();
    }

    protected function initialData(mixed $instanceId): array
    {
        if (! $step = $this->extractStepFromRequest()) {
            return $this->initialDataForFirstStep($instanceId);
        }

        $methodName = 'initialDataForStep'.Str::ucfirst(Str::camel($step));

        return method_exists($this, $methodName)
            ? $this->$methodName($instanceId)
            : $this->initialDataForStep($step, $instanceId);
    }

    protected function initialDataForFirstStep(mixed $instanceId): array
    {
        return [];
    }

    protected function initialDataForStep(string $step, mixed $instanceId): array
    {
        return [];
    }
}
