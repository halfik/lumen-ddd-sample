<?php

namespace App\Exceptions;

use Domains\Common\Exceptions\DomainAuthException;
use Domains\Common\Exceptions\DomainException;
use Domains\Common\Exceptions\InvalidUUIDException;
use Domains\Integrations\Exceptions\Google\IntegrationExceptionContract;
use Domains\Integrations\Exceptions\IntegrationDomainException;
use Domains\Marketing\Exceptions\SequenceDomainException;
use Firebase\JWT\ExpiredException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @OA\Schema (
 *     schema="DomainAuthException",
 *     type="object",
 *     @OA\Property (
 *       property="type",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="message",
 *       type="object",
 *         @OA\Property (
 *             property="key",
 *             type="string",
 *             enum={"forbidden_action","user_not_member_of_company"}
 *         ),
 *         @OA\Property (
 *             property="msg",
 *             type="string",
 *         ),
 *     ),
 * ),
 *
 * @OA\Schema (
 *     schema="DomainException",
 *     type="object",
 *     @OA\Property (
 *       property="type",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="message",
 *       type="object",
 *         @OA\Property (
 *             property="key",
 *             type="string",
 *             enum={
 *               "account_already_active",
 *               "account_status_not_pending",
 *               "account_status_transition_not_allowed",
 *               "cant_grant_company_owner_role",
 *               "cant_grant_role",
 *               "cant_change_company_owner_role",
 *               "not_found",
 *               "event_applied_on_wrong_model",
 *             }
 *         ),
 *         @OA\Property (
 *             property="msg",
 *             type="string",
 *         ),
 *     ),
 * )
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        \Domains\Common\Exceptions\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $e
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        $class = get_class($e);

        switch ($class) {
            case HttpResponseException::class:
                $response = $e->getResponse();
                break;
            case AuthenticationException::class:
            case AuthorizationException::class:
            case ExpiredException::class:
                $response = new JsonResponse(
                    [
                        'type' => 'authException',
                        'message' => [
                            'key' => 'auth',
                            'msg' => $e->getMessage(),
                        ]
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
                break;
            case DomainAuthException::class:
                /** @var  DomainAuthException $e */
                $response = new JsonResponse(
                    [
                        'type' => 'domainAuthException',
                        'message' => [
                            'key' => $e->type(),
                            'msg' => $e->getMessage(),
                        ],
                    ],
                    Response::HTTP_FORBIDDEN
                );
                break;
            case DomainException::class:
                /** @var  DomainException $e */
                $response = new JsonResponse(
                    [
                        'type' => 'domainException',
                        'message' => [
                            'key' => $e->type(),
                            'msg' =>  $e->getMessage(),
                        ],
                    ],
                    Response::HTTP_BAD_REQUEST
                );
                break;
            case \RuntimeException::class:
                $response = new JsonResponse(
                    [
                        'type' => 'runtimeException',
                        'message' => [
                            'key' => 'runtime',
                            'msg' => $e->getMessage(),
                        ],
                    ],
                    Response::HTTP_BAD_REQUEST
                );
                break;
            case ValidationException::class:
            case \Domains\Common\Exceptions\ValidationException::class:
                /** @var  ValidationException $e */
                $response = new JsonResponse(
                    [
                        'type' => 'requestInputValidation',
                        'message' => $e->getMessage(),
                        'fields_errors' => $e->errors(),
                    ],
                    Response::HTTP_BAD_REQUEST
                );
                break;
            case \InvalidArgumentException::class:
                $response = new JsonResponse(
                    [
                        'type' => 'invalidArgumentException',
                        'message' => [
                            'key' => 'invalidArgument',
                            'msg' => $e->getMessage(),
                        ]
                    ],
                    Response::HTTP_BAD_REQUEST
                );
                break;
            case InvalidUUIDException::class:
            case BadRequestException::class:
                $response = new JsonResponse(
                    [
                        'type' => 'badRequestException',
                        'message' => [
                            'key' => 'badRequest',
                            'msg' =>  $e->getMessage(),
                        ],
                    ],
                    Response::HTTP_BAD_REQUEST
                );
                break;
            case ModelNotFoundException::class:
                /** @var  ModelNotFoundException $e */
                $modelName = explode('\\', $e->getModel());
                $ids = $e->getIds();
                if (is_array($ids)) {
                    $ids =  implode(', ',$ids);
                }
                $msg = sprintf(
                    '%s entity %s not found',
                    $modelName[count($modelName)-1],
                    $ids
                );

                $response = new JsonResponse(
                    [
                        'type' => 'notFoundException',
                        'message' => [
                            'key' => 'model',
                            'msg' => $msg,
                        ]
                    ],
                    Response::HTTP_NOT_FOUND
                );
                break;
            default:
                $response = $this->prepareJsonResponse($request, $e);

                if ($e instanceof \Doctrine\DBAL\Exception) {
                    $codes = config('database.errors.dbal');
                    foreach ($codes as $code) {
                        if(str_contains($e->getMessage(), 'SQLSTATE['.$code.']')){
                            $response = new JsonResponse(
                                [
                                    'type' => 'serviceUnavailableException',
                                    'message' => [
                                        'type' => 'dbal',
                                        'msg' =>  'We are experiencing technical difficulties. Please try again later.',
                                    ],
                                ],
                                Response::HTTP_SERVICE_UNAVAILABLE
                            );
                            break;
                        }
                    }
                } elseif ($e instanceof \PDOException) {
                    $codes = config('database.errors.pdo');
                    foreach ($codes as $code) {
                        if(str_contains($e->getMessage(), 'SQLSTATE['.$code.']')){
                            $response = new JsonResponse(
                                [
                                    'type' => 'serviceUnavailableException',
                                    'message' => [
                                        'key' => 'pdo',
                                        'msg' => 'We are experiencing technical difficulties. Please try again later.',
                                    ],
                                ],
                                Response::HTTP_SERVICE_UNAVAILABLE
                            );
                            break;
                        }
                    }
                }
        }

        return $response;
    }
}
