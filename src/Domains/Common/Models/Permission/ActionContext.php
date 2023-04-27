<?php

namespace Domains\Common\Models\Permission;

class ActionContext
{
    private ActionPerformedByContract $performedBy;
    private ?ActionPerformedOnContract $performedOn;
    private array $params;

    /**
     * @param ActionPerformedByContract      $performedBy
     * @param ActionPerformedOnContract|null $performedOn
     * @param array                          $params
     */
    public function __construct(ActionPerformedByContract $performedBy, ?ActionPerformedOnContract $performedOn, array $params=[])
    {
        $this->performedBy = $performedBy;
        $this->performedOn = $performedOn;
        $this->params = $params;
    }

    /**
     * @return ActionPerformedByContract
     */
    public function performedBy(): ActionPerformedByContract
    {
        return $this->performedBy;
    }

    /**
     * @return ActionPerformedOnContract|null
     */
    public function performedOn(): ?ActionPerformedOnContract
    {
        return $this->performedOn;
    }

    /**
     * @return array
     */
    public function params(): array
    {
        return $this->params;
    }

    /**
     * Is company user performing action on himself?
     * @return bool
     */
    public function isPerformedOnHimself(): bool
    {
        if(!$this->performedOn()) {
            return false;
        }
        return $this->performedBy()->uuid()->equals($this->performedOn()->uuid());
    }
}
