<?php

namespace Code16\Sharp\Utils\Entities;

use Code16\Sharp\Exceptions\SharpInvalidEntityKeyException;

class SharpEntityManager
{
    public function entityFor(string $entityKey): SharpEntity|SharpDashboardEntity
    {
        if(!$entity = config("sharp.entities.$entityKey")) {
            if(!$entity = config("sharp.dashboards.$entityKey")) {
                throw new SharpInvalidEntityKeyException("The entity [{$entityKey}] was not found.");
            }
        }
        
        if(is_string($entity)) {
            // New Sharp 7 format: SharpEntity
            return new $entity($entityKey);
        }
        
        // Old array config format is used
        if(isset($this->entity["view"])) {
            // This is a Dashboard
            return new class($entity, $entityKey) extends SharpDashboardEntity {
                private array $entity;

                public function __construct(array $entity, string $entityKey)
                {
                    parent::__construct($entityKey);
                    $this->entity = $entity;
                    $this->view = $this->entity["view"];
                    $this->policy = $this->entity["policy"] ?? null;
                }
            };
        }
        
        return new class($entity, $entityKey) extends SharpEntity {
            private array $entity;

            public function __construct(array $entity, string $entityKey)
            {
                parent::__construct($entityKey);
                $this->entity = $entity;
                $this->label = $this->entity["label"] ?? "Entity";
                $this->isSingle = $this->entity["single"] ?? false;
                $this->list = $this->entity["list"] ?? null;
                $this->show = $this->entity["show"] ?? null;
                $this->form = $this->entity["form"] ?? null;
                $this->policy = $this->entity["policy"] ?? null;
            }

            public function getMultiforms(): array
            {
                return collect($this->entity["forms"] ?? [])
                    ->mapWithKeys(function ($values, $key) {
                        return [$key => [$values["form"], $values["label"]]];
                    })
                    ->toArray();
            }
        };
    }
}