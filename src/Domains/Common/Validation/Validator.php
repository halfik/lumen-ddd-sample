<?php

namespace Domains\Common\Validation;

use Domains\Common\Exceptions\ValidationException;

class Validator
{
    private const EMAIL_MAX = 100;
    private const PHONE_MAX = 30;

    protected ValidationException $exception;

    public function __construct()
    {
        $this->exception = new ValidationException();
    }

    /**
     * @return ValidationException
     */
    public function exception(): ValidationException
    {
        return $this->exception;
    }

    /**
     * @param string      $email
     * @param string      $field
     * @return $this
     */
    public function email(string $email, string $field = 'email'): self
    {
        if (strlen($email) > self::EMAIL_MAX) {
            $this->exception()->addError($field, 'max', [self::EMAIL_MAX]);
        }
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->exception()->addError($field, 'invalid_email', [$email]);
        }

        return $this;
    }

    /**
     * @param string $phone
     * @param string $field
     * @return $this
     */
    public function phone(string $phone, string $field = 'phone'): self
    {
        if (strlen($phone) > self::PHONE_MAX) {
            $this->exception()->addError($field, 'max', [self::PHONE_MAX]);
        }

        if (strlen($phone) == 0) {
            $this->exception()->addError($field, 'empty', []);
        }

        $pattern = "/^\\(*\\+?[1-9\\s]{2,6}\\)*[0-9\\s-]{4,15}$/";
        if (preg_match($pattern, $phone) == false) {
            $this->exception()->addError($field, 'invalid', []);
        }
        
        return $this;
    }
}
