<?php

namespace App\Http\Responses\AdminPanel\Accounts;

use App\Http\Mappers\AdminPanel\Accounts\ApiCompanyAccount;
use Domains\AdminPanel\Models\Accounts\CompanyAccount;
use Illuminate\Http\JsonResponse;

class BlockCompanyAccountResponse extends JsonResponse
{
    /**
     * @param CompanyAccount $companyAccount
     */
    public function __construct(CompanyAccount $companyAccount)
    {
        $apiModel = new ApiCompanyAccount($companyAccount);

        parent::__construct(
            $apiModel->toArray(),
            self::HTTP_ACCEPTED
        );
    }
}
