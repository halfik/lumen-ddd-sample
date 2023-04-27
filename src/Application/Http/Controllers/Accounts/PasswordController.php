<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Responses\Accounts\ChangePasswordResponse;
use Doctrine\ORM\EntityManagerInterface;
use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Domains\Accounts\Models\User\User;
use Domains\Accounts\Repositories\PasswordResetRepositoryContract;
use Domains\Accounts\Repositories\UserRepositoryContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    private UserRepositoryContract $userRepository;
    private PasswordResetRepositoryContract $passwordResetRepository;

    /**
     * @param UserRepositoryContract     $userRepository
     * @param PasswordResetRepositoryContract $passwordResetRepository
     */
    public function __construct(
        UserRepositoryContract $userRepository,
        PasswordResetRepositoryContract $passwordResetRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->passwordResetRepository = $passwordResetRepository;
    }

    /**
     * Change logged user password
     * @param Request $request
     * @return ChangePasswordResponse
     * @throws ValidationException
     * @throws \Domains\Common\Exceptions\DomainAuthException
     * @throws \Domains\Common\Exceptions\ValidationException
     *
     *  @OA\Put  (
     *     tags={"User"},
     *     path="/api/password",
     *     @OA\Header(
     *         header="Authorization",
     *         description="JWT",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password_confirmation",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="current_password",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Success",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request."
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication failed."
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Error: Action forbidden. Logged user dont have permission to perform requested action."
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Model required to perform action not found."
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     *  )
     */
    public function changeOwn(Request $request): ChangePasswordResponse
    {
        $this->validate($request, [
            'password' => ['required'],
            'password_confirmation' => ['required'],
            'current_password' => ['required'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $userCompanyAccount = $user->userCompanyAccounts(UserCompanyAccountStatus::active())->first();
        $this->requireModel($userCompanyAccount, UserCompanyAccount::class, []);

        $user->changePassword(
            $this->userRepository,
            $request->get('password'),
            $request->get('password_confirmation'),
            $request->get('current_password'),
            $userCompanyAccount
        );

        $this->userRepository->flush();

        return new ChangePasswordResponse();
    }
}
