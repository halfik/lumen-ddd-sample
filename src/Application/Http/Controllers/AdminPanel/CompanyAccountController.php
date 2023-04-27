<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Responses\AdminPanel\Accounts\BlockCompanyAccountResponse;
use App\Http\Responses\AdminPanel\Accounts\UnblockCompanyAccountResponse;
use Domains\AdminPanel\Models\Accounts\CompanyAccount;
use Domains\AdminPanel\Repositories\CompanyAccountRepositoryContract;
use Domains\Common\Models\AggregateRootId;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class CompanyAccountController extends Controller
{
    private CompanyAccountRepositoryContract $companyAccountRepository;

    /**
     * @param CompanyAccountRepositoryContract $companyAccountRepository
     * @param Request                          $request
     */
    public function __construct(
        CompanyAccountRepositoryContract $companyAccountRepository,
        Request $request
    )
    {
        $this->companyAccountRepository = $companyAccountRepository;

        // will trigger api middleware check for role to access endpoint
        $request->user();
    }

    /**
     * Block company account
     *
     * @param string $companyId
     * @return BlockCompanyAccountResponse
     *
     * @OA\Put(
     *     tags={"AdminPanel", "CompanyAccount"},
     *     path="/api/admin-panel/company-accounts/{companyId}/block",
     *      @OA\Header(
     *         header="Authorization",
     *         description="JWT",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="companyId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/AdminPanelCompanyAccount")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. Missed some query parameters or validation failed."
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication failed."
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function block(string $companyId): BlockCompanyAccountResponse
    {
        $companyAccount = $this->companyAccountRepository->findById(new AggregateRootId($companyId));
        $this->requireModel($companyAccount, CompanyAccount::class, [$companyId]);

        $companyAccount->block($this->companyAccountRepository);
        $this->companyAccountRepository->flush();

        return new BlockCompanyAccountResponse($companyAccount);
    }

    /**
     * Unblock company account
     *
     * @param string $companyId
     * @return UnblockCompanyAccountResponse
     *
     *  @OA\Put(
     *     tags={"AdminPanel", "CompanyAccount"},
     *     path="/api/admin-panel/company-accounts/{companyId}/unblock",
     *      @OA\Header(
     *         header="Authorization",
     *         description="JWT",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="companyId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/AdminPanelCompanyAccount")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. Missed some query parameters or validation failed."
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication failed."
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function unblock(string $companyId): UnblockCompanyAccountResponse
    {
        $companyAccount = $this->companyAccountRepository->findById(new AggregateRootId($companyId));
        $this->requireModel($companyAccount, CompanyAccount::class, [$companyId]);

        $companyAccount->unblock($this->companyAccountRepository);
        $this->companyAccountRepository->flush();

        return new UnblockCompanyAccountResponse($companyAccount);
    }
}
