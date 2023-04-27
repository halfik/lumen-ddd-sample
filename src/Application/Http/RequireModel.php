<?php

namespace App\Http;

use Illuminate\Database\Eloquent\ModelNotFoundException;

trait RequireModel
{
    /**
     * Ensure we have model needed to perform action
     * @param mixed       $model
     * @param string      $class
     * @param array       $modelIds
     * @throws ModelNotFoundException
     */
    protected function requireModel(mixed $model, string $class, array $modelIds): void
    {
        if (!$model) {
            $exception = new ModelNotFoundException();
            $exception->setModel($class, $modelIds);
            throw $exception;
        }
    }
}
