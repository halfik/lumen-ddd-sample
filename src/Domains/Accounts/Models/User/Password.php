<?php

namespace Domains\Accounts\Models\User;

use Domains\Common\Models\ValueObject;

class Password implements ValueObject
{
    private string $hashedPassword;
    private const HASH_COST = 10;

    /**
     * @param string $hashedPassword
     */
    public function __construct(string $hashedPassword)
    {
        $this->hashedPassword = $hashedPassword;
    }

    /**
     * Hash password
     * @param string $plainPassword
     * @return static
     */
    public static function hash(string $plainPassword): self
    {
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT, [
            'cost' => self::HASH_COST,
        ]);

        return new self($hash);
    }

    /**
     * Verify if plain text password is same as current hashed one
     * @param string $plainPassword
     * @return bool
     */
    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, (string)$this);
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return [
            'hashed_password' => $this->hashedPassword,
        ];
    }

    /**
     * @param array $data
     * @return static
     */
    public static function fromRawData(array $data): static
    {
        return new self(
            $data['hashed_password']
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
        return $this->hashedPassword;
    }
}
