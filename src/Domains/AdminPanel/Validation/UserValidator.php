<?php

namespace Domains\AdminPanel\Validation;

use Domains\AdminPanel\Models\Accounts\User;
use Domains\AdminPanel\Repositories\UserRepositoryContract;
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
}
