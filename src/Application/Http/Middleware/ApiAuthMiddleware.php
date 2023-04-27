<?php

namespace App\Http\Middleware;

use Domains\Accounts\Models\Company\CompanyAccountId;
use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Domains\Accounts\Repositories\UserRepositoryContract;
use Domains\Common\Models\Auth\AuthToken;
use Domains\Common\Models\Permission\Role;
use Firebase\JWT\ExpiredException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiAuthMiddleware
{
    private array $verifyTokenUrls = [
        '/api/accounts/activate',
        '/api/user',
    ];

    /**
     * @param Request     $request
     * @param \Closure    $next
     * @param string|null $roleName
     * @return mixed
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function handle(Request $request, \Closure $next, ?string $roleName = null)
    {
        // user id can be in route params or in request body
        $auth = $request->header('Authorization');
        if ($auth) {
            $this->authUser($auth, $request, $roleName);
        } else {
            throw new AuthorizationException('Not authorized');
        }

        return $next($request);
    }

    /**
     * Authenticate user
     * @param string $token
     * @param Request $request
     * @param string|null $roleName
     * @throws AuthenticationException
     * @throws ExpiredException
     */
    private function authUser(string $token, Request $request,  ?string $roleName): void
    {
        try {
            $authToken = AuthToken::decode($token);
        } catch (\Exception $exception) {
            throw new AuthenticationException('unable_to_decode_JWT');
        }

        if ($authToken->expiresAt() < time()) {
            throw new AuthenticationException('JWT_expired');
        }

        if (!$authToken->userId()) {
            throw new BadRequestException('JWT_no_data');
        }

        $request->setUserResolver(function () use($authToken, $request, $roleName) {
            /** @var UserRepositoryContract $userRepository */
            $userRepository = app(UserRepositoryContract::class);
            $user = $userRepository->findById($authToken->userId());

            if (!$user) {
                throw new AuthenticationException();
            }

            // check if user has required role
            if ($roleName) {
                $companyAccountIdRaw = $request->companyAccountId ?? $request->get('company_account_id');
                $companyAccountId = $companyAccountIdRaw ? new CompanyAccountId($companyAccountIdRaw) : null;
                $requiredRole = Role::fromName($roleName);

                if (!$user->hasRole($requiredRole, $companyAccountId)) {
                    throw new NotFoundHttpException();
                }
            }

            // for account verify/activate endpoints we account will be pending
            if (in_array($request->getRequestUri(), $this->verifyTokenUrls)) {
                return $user;
            }

            // account is not active on verify process
            $activeAccounts = $user->userCompanyAccounts(UserCompanyAccountStatus::active());
            if($activeAccounts->count() === 0) {
                throw new AuthenticationException('account_not_active');
            }

            return $user;
        });
    }
}
