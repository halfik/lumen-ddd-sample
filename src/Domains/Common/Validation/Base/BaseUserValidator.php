<?php

namespace Domains\Common\Validation\Base;

use Domains\Common\Models\AggregateRootId;
use Domains\Common\Validation\Validator;

abstract class BaseUserValidator extends Validator
{
    protected const NAME_MIX = 2;
    protected const NAME_MAX = 30;
    protected const PASSWORD_MIN = 8;
    protected const COUNTRY_MAX = 3;
    protected const TITLE_MIN = 1;
    protected const TITLE_MAX = 255;

    abstract protected function isEmailInUse(string $email, ?AggregateRootId $editedUserId): bool;

    /**
     * @param string               $email
     * @param AggregateRootId|null $editedUserId
     * @return $this
     */
    public function emailUnique(string $email, ?AggregateRootId $editedUserId = null): self
    {
        if ($this->isEmailInUse($email, $editedUserId)) {
            $this->exception()->addError('email', 'in_use');
        }

        return $this;
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function firstName(string $firstName): self
    {
        if (strlen($firstName) < self::NAME_MIX) {
            $this->exception()->addError('first_name', 'min', [self::NAME_MIX]);
        }
        if (strlen($firstName) > self::NAME_MAX) {
            $this->exception()->addError('first_name', 'max', [self::NAME_MAX]);
        }
        if (!preg_match('/^[a-zA-Z.\s]+$/i', $firstName)) {
            $this->exception()->addError('first_name', 'only_letters', []);
        }

        return $this;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function lastName(string $lastName): self
    {
        if (strlen($lastName) < self::NAME_MIX) {
            $this->exception()->addError('last_name', 'min', [self::NAME_MIX]);
        }
        if (strlen($lastName) > self::NAME_MAX) {
            $this->exception()->addError('last_name', 'max', [self::NAME_MAX]);
        }
        if (!preg_match('/^[a-zA-Z.\s]+$/i', $lastName)) {
            $this->exception()->addError('last_name', 'only_letters', []);
        }

        return $this;
    }
}
