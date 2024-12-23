<?php

namespace Code16\Sharp\Show\Layout;

use Code16\Sharp\Form\Layout\HasLayout;
use Illuminate\Support\Traits\Conditionable;

class ShowLayout implements HasLayout
{
    use Conditionable;

    protected array $sections = [];

    final public function addSection(string $label, ?\Closure $callback = null): self
    {
        $section = new ShowLayoutSection($label);
        $this->sections[] = $section;

        if ($callback) {
            $callback($section);
        }

        return $this;
    }

    final public function addEntityListSection(string $entityListKey, ?bool $collapsable = null): self
    {
        $section = new ShowLayoutSection('');
        $section->addColumn(12, function ($column) use ($entityListKey) {
            $column->withSingleField($entityListKey);
        });

        if ($collapsable !== null) {
            $section->setCollapsable($collapsable);
        }

        $this->sections[] = $section;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'sections' => collect($this->sections)
                ->map(fn ($section) => $section->toArray())
                ->all(),
        ];
    }
}
