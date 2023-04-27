<?php

namespace Domains\Accounts\Models\Company;

use Domains\Common\Models\Account\UserCompanyAccountStatusContract;
use Domains\Common\Models\ValueObject;

class UserCompanyAccountStatus implements
    ValueObject,
    UserCompanyAccountStatusContract
{
    private string $status;

    /**
     * @param string $status
     * @throws \RuntimeException
     */
    public function __construct(string $status)
    {
        if (!in_array($status, self::ALL_STATUSES)) {
            $msg = sprintf('%s status not supported', $status);
            throw new \InvalidArgumentException($msg);
        }
        $this->status = $status;
    }

    /**
     * @return static
     */
    public static function active(): self
    {
        return new self(self::STATUS_ACTIVE);
    }

    /**
     * @return static
     */
    public static function inactive(): self
    {
        return new self(self::STATUS_INACTIVE);
    }

    /**
     * @return static
     */
    public static function pending(): self
    {
        return new self(self::STATUS_PENDING);
    }

    /**
     * Check if statuses are same
     * @param UserCompanyAccountStatus $oStatus
     * @return bool
     */
    public function same(UserCompanyAccountStatus $oStatus): bool
    {
        return $this->status() === $oStatus->status();
    }

    /**
     * Can status transition into another status?
     * @param UserCompanyAccountStatus $oStatus
     * @return bool
     */
    public function isTransitionAllowed(UserCompanyAccountStatus $oStatus): bool
    {
        return in_array($oStatus->status, self::ALLOWED_TRANSITIONS[$this->status()] ?? []);
    }

    /**
     * @return string
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status(),
        ];
    }

    /**
     * @param array $data
     * @return static
     */
    public static function fromRawData(array $data): static
    {
        return new self(
            $data['status']
        );
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_HEX_TAG);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->status;
    }
}

