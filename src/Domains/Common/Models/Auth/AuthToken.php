<?php

namespace Domains\Common\Models\Auth;

use Domains\Accounts\Models\User\UserId;
use Domains\Common\Models\Account\UserContract;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthToken
{
    private string $token;
    private string $type;
    private int $expiresAt;
    private array $payload;

    /**
     * @param string $token
     * @param array  $payload
     * @param int    $expiresAt
     */
    public function __construct(string $token, array $payload, int $expiresAt)
    {
        $this->token = $token;
        $this->type = 'Bearer';
        $this->expiresAt = $expiresAt;
        $this->payload = $payload;
    }

    /**
     * @param UserContract     $user
     * @param int|null $expireAt
     * @param array    $data
     * @return static
     */
    public static function encodeFromUser(UserContract $user, int $expireAt = null, array $data = []): self
    {
        if (!$expireAt) {
            $expireAt = time() + env('JWT_LIFE_TIME');
        }
        $payload = [
            'iss' => env('APP_URL'),
            'aud' => env('APP_URL'),
            'iat' => time(),
            'nbf' => time(),
            'exp' => $expireAt,
            'data' => [
                'user_id' => (string)$user->uuid(),
            ],
        ];

        if($data) {
            $payload['data']  = array_merge($data, $payload['data']);
        }

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');
        return new self($token, $payload, $payload['exp']);
    }

    /**
     * @param string $token
     * @return static
     */
    public static function decode(string $token): self
    {
        $token = str_replace('Bearer ', '', $token);
        $payload = (array)JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
        return new self($token, $payload, $payload['exp']);
    }

    /**
     * @return string
     */
    public function token(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function expiresAt(): int
    {
        return $this->expiresAt;
    }

    /**
     * @return array
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public function payloadData(?string $key = null): mixed
    {
        if ($key &&  $this->payload['data'] ){
            $data = (array)$this->payload['data'];
            return $data[$key] ?? null;
        }

        return $this->payload['data'] ?? null;
    }


    /**
     * @return UserId|null
     */
    public function userId(): ?UserId
    {
        $uid = $this->payloadData()->user_id ?? null;
        return is_null($uid) ? null : new UserId($uid);
    }
}
