<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Responses\Accounts\RegisterUserResponse;
use App\Http\Responses\Accounts\UserDetailsResponse;
use App\Http\Responses\Accounts\VerifyUserResponse;
use Doctrine\ORM\EntityManagerInterface;
use Domains\Accounts\Models\User\User;
use Domains\Accounts\Models\User\UserId;
use Domains\Accounts\Repositories\UserRepositoryContract;
use Domains\Sales\Repositories\WorkflowRepositoryContract;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    private UserRepositoryContract $userRepository;
    private WorkflowRepositoryContract $workflowRepository;

    /**
     * @param UserRepositoryContract     $userRepository
     * @param WorkflowRepositoryContract $workflowRepository
     */
    public function __construct(
        UserRepositoryContract $userRepository,
        WorkflowRepositoryContract $workflowRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->workflowRepository = $workflowRepository;
    }

    /**
     * Register new user
     *
     * @param Request $request
     * @return RegisterUserResponse
     * @throws ValidationException
     * @throws \Domains\Common\Exceptions\ValidationException
     *
     * @OA\Post (
     *     tags={"User"},
     *     path="/api/users",
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
     *     @OA\Parameter(
     *         name="password_confirmation",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="first_name",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="last_name",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. Missed some query parameters or validation failed."
     *     ),
     * )
     */
    public function register(Request $request): RegisterUserResponse
    {
        $this->validate(
            $request,
            [
                'email' => 'required',
                'password' =>  'required',
                'password_confirmation' =>  'required',
                'first_name' => 'required',
                'last_name' => 'required',
            ]
        );

        /** @var EntityManagerInterface $em */
        $em = app(EntityManagerInterface::class);
        $em->beginTransaction();

        try {
            User::register(
                $this->userRepository,
                $this->workflowRepository,
                $this->mailer(),
                $request->get('email', ''),
                $request->get('password', ''),
                $request->get('password_confirmation', ''),
                $request->get('first_name', ''),
                $request->get('last_name', '')
            );

            $this->userRepository->flush();
            $this->workflowRepository->flush();

            $em->commit();
        } catch (\Exception $exception) {
            $em->rollback();
            throw $exception;
        }

        return new RegisterUserResponse();
    }
}
