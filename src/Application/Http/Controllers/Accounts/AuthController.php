<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Responses\Accounts\AuthenticateResponse;
use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Domains\Accounts\Repositories\UserRepositoryContract;
use Domains\Common\Models\Auth\AuthToken;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private UserRepositoryContract $userRepository;

    /**
     * @param UserRepositoryContract     $userRepository
     */
    public function __construct(UserRepositoryContract $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Login
     * @param Request $request
     * @return AuthenticateResponse
     * @throws ValidationException
     * @throws AuthenticationException
     *
     * @OA\Post (
     *     tags={"User", "Auth"},
     *     path="/api/login",
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="JWT Token that can be used to authenicate as user. There will be no token data in case or failure.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthToken")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When provided user id is incorrect."
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication failed."
     *     ),
     * )
     */
    public function authenticate(Request $request): AuthenticateResponse
    {
        $this->validate(
            $request,
            [
                'email' => 'required',
                'password' =>  'required',
            ]
        );

        #@TODO: move login to user model
        $user = $this->userRepository->findOneByEmail($request->get('email', ''));
        if (!$user || !$user->verifyPassword($request->get('password', ''))){
            throw new AuthenticationException();
        }

        $activeAccounts = $user->userCompanyAccounts(UserCompanyAccountStatus::active());
        if($activeAccounts->count() === 0) {
            throw new AuthenticationException('account_not_active');
        }
        $token = AuthToken::encodeFromUser($user);
        return new AuthenticateResponse($token);
    }
}
