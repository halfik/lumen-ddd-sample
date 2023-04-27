<?php

namespace Infrastructure\Persistence\Database\Seeders;

use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Domains\Accounts\Models\User\Password;
use Domains\Common\Models\Permission\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemUsersSeeder extends Seeder
{
    private const ENV_VARIABLES = [
        'SYSTEM_COMPANY_NAME',
        'SYSTEM_ADMIN_EMAIL',
        'SYSTEM_ADMIN_PASSWORD',
        'SYSTEM_ADMIN_FIRST_NAME',
        'SYSTEM_ADMIN_LAST_NAME'
    ];

    private const COMPANY_ACCOUNT_UUID = [
        '6006d2f4-d6bd-4166-98aa-97153b334a7e'
    ];
    private const USER_UUID = [
        '4ca49b1d-7c95-4773-b31f-31924f103119'
    ];
    private const USER_COMPANY_ACCOUNT_UUID = [
        '2755ed9f-a027-4660-9be0-31d093c4d198', '287d6e88-e671-4d23-a823-6ea92d53ac29' // 2 roles for user
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $this->ensureEnvVariables();

        $this->createCompanyAccount(
            self::COMPANY_ACCOUNT_UUID[0],
            $_ENV['SYSTEM_COMPANY_NAME']
        );

        $this->createUser(
            self::USER_UUID[0],
            $_ENV['SYSTEM_ADMIN_EMAIL'],
            $_ENV['SYSTEM_ADMIN_FIRST_NAME'],
            $_ENV['SYSTEM_ADMIN_LAST_NAME'],
            $_ENV['SYSTEM_ADMIN_PASSWORD']
        );

        $this->linkUserToCompany(
            self::USER_COMPANY_ACCOUNT_UUID[0],
            self::USER_UUID[0],
            self::COMPANY_ACCOUNT_UUID[0],
            Role::systemAdmin()
        );

        $this->linkUserToCompany(
            self::USER_COMPANY_ACCOUNT_UUID[1],
            self::USER_UUID[0],
            self::COMPANY_ACCOUNT_UUID[0],
            Role::companyOwner()
        );
    }

    /**
     * Ensure all env variables are configured
     */
    private function ensureEnvVariables(): void
    {
        $missing = [];
        foreach (self::ENV_VARIABLES as $name) {
            if(!getenv($name)) {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            exit("ENV variables missing: ". implode(', ', $missing) ."\n");
        }
    }

    /**
     * @param string $uuid
     * @param string $name
     */
    private function createCompanyAccount(string $uuid, string $name): void
    {
        DB::table('company_accounts')->updateOrInsert(
            ['id' => $uuid],
            [
                'name' => $name,
                'is_active' => true,
                'version' => 1,
                'created_at' => new \DateTimeImmutable(),
                'updated_at' => new \DateTimeImmutable(),

            ]
        );
    }

    /**
     * @param string $uuid
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $password
     */
    private function createUser(string $uuid, string $email, string $firstName, string $lastName, string $password): void
    {
        DB::table('users')->updateOrInsert(
            ['id' => $uuid],
            [
                'password' => Password::hash($password),
                'email' => $email,
                'email_verified_at' => new \DateTimeImmutable(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'display_name' => $firstName . ' ' . $lastName,
                'version' => 1,
                'created_at' => new \DateTimeImmutable(),
                'updated_at' => new \DateTimeImmutable(),

            ]
        );
    }

    /**
     * @param string $uuid
     * @param string $userId
     * @param string $companyId
     * @param Role   $role
     */
    private function linkUserToCompany(string $uuid, string $userId, string $companyId, Role $role): void
    {
        DB::table('users__company_accounts')->updateOrInsert(
            ['id' => $uuid],
            [
                'user__id' => $userId,
                'company_account__id' => $companyId,
                'role' => (string)$role,
                'status' => (string)UserCompanyAccountStatus::active(),
                'version' => 1,
                'created_at' => new \DateTimeImmutable(),
                'updated_at' => new \DateTimeImmutable(),

            ]
        );
    }
}
