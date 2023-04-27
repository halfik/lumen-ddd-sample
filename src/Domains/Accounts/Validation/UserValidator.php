<?php

namespace Domains\Accounts\Validation;

use Domains\Accounts\Models\User\Password;
use Domains\Accounts\Models\User\User;
use Domains\Accounts\Repositories\UserRepositoryContract;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Validation\Base\BaseUserValidator;

class UserValidator extends BaseUserValidator
{

    private UserRepositoryContract $userRepository;

    /**
     * @param UserRepositoryContract $userRepository
     */
    public function __construct(UserRepositoryContract $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    /**
     * @param string               $email
     * @param AggregateRootId|null $editedUserId
     * @return bool
     */
    protected function isEmailInUse(string $email, ?AggregateRootId $editedUserId): bool
    {
       return $this->userRepository->findOneByEmail($email, $editedUserId) instanceof User;
    }

    /**
     * @param string $country
     * @return $this
     */
    public function country(string $country): self
    {
        if (strlen($country) > self::COUNTRY_MAX) {
            $this->exception()->addError('country', 'max', [self::COUNTRY_MAX]);
        }
        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function password(string $password): self
    {
        if (strlen($password) < self::PASSWORD_MIN) {
            $this->exception()->addError('password', 'min', [self::PASSWORD_MIN]);
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $this->exception()->addError('password', 'require_special_character', []);
        }
        if (!preg_match('/[a-z]/', $password)) {
            $this->exception()->addError('password', 'require_lower_case_letters', []);
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $this->exception()->addError('password', 'require_upper_case_letters', []);
        }
        if (!preg_match('/[0-9]/', $password)) {
            $this->exception()->addError('password', 'require_numbers', []);
        }
        return $this;
    }

    /**
     * @param string $password
     * @param string $confirmation
     * @return $this
     */
    public function passwordConfirmation(string $password, string $confirmation): self
    {
        if ($password !== $confirmation) {
            $this->exception()->addError('password', 'password_confirmation');
        }
        return $this;
    }

    /**
     * @param Password $password
     * @param string $currentPassword
     * @return $this
     */
    public function passwordVerify(Password $password, string $currentPassword): self
    {
        if (!$password->verify($currentPassword)) {
            $this->exception()->addError('current_password', 'password_not_match');
        }
        return $this;
    }

    /**
     * @param string      $phone
     * @param string|null $field
     * @return $this
     */
    public function phone(string $phone,  ?string $field = 'phone_number'): self
    {
        return parent::phone($phone, $field);
    }

    /**
     * @param string $title
     * @return $this
     */
    public function title(string $title): self
    {
        if (strlen($title) < self::TITLE_MIN) {
            $this->exception()->addError('title', 'min', [self::TITLE_MIN]);
        }
        if (strlen($title) > self::TITLE_MAX) {
            $this->exception()->addError('title', 'max', [self::TITLE_MAX]);
        }

        return $this;
    }
}
