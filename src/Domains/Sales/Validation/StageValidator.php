<?php

namespace Domains\Sales\Validation;

use Domains\Common\Validation\Validator;

class StageValidator extends Validator
{
    private const NAME_MIN = 3;
    private const NAME_MAX = 255;

    /**
     * @param string $value
     * @return $this
     */
    public function name(string $value): self
    {
        if (strlen($value) < self::NAME_MIN) {
            $this->exception()->addError('name', 'min', [self::NAME_MIN]);
        }
        if (strlen($value) > self::NAME_MAX) {
            $this->exception()->addError('name', 'max', [self::NAME_MAX]);
        }
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function position(int $value): self
    {
        if ($value < 1) {
            $this->exception()->addError('position', 'min', [1]);
        }
        return $this;
    }
}
