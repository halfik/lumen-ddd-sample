<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Responses\Sales\CreateStageResponse;
use Domains\Accounts\Models\Company\CompanyAccount;
use Domains\Accounts\Models\Company\CompanyAccountId;
use Domains\Accounts\Models\User\User;
use Domains\Sales\Models\Workflow\Stage;
use Domains\Sales\Models\Workflow\StageId;
use Domains\Sales\Models\Workflow\Workflow;
use Domains\Sales\Models\Workflow\WorkflowId;
use Domains\Sales\Repositories\WorkflowRepositoryContract;
use Illuminate\Http\Request;

class StageController extends Controller
{
    private WorkflowRepositoryContract $workflowRepository;

    /**
     * @param WorkflowRepositoryContract $workflowRepository
     */
    public function __construct(WorkflowRepositoryContract $workflowRepository)
    {
        $this->workflowRepository = $workflowRepository;
    }

    /**
     * Create new workflow stage
     * @param string $workflowId
     * @param Request $request
     * @return CreateStageResponse
     * @throws \Domains\Common\Exceptions\DomainAuthException
     * @throws \Domains\Common\Exceptions\DomainException
     * @throws \Domains\Common\Exceptions\ValidationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @OA\Post (
     *     tags={"Workflow"},
     *     path="/api/workflows/{workflowId}/stages",
     *     @OA\Header(
     *       header="Authorization",
     *       required=true,
     *       @OA\Schema(
     *           type="string"
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="workflowId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *       name="name",
     *       in="query",
     *       required=true,
     *     ),
     *     @OA\Parameter(
     *       name="position",
     *       in="query",
     *       required=false,
     *       @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Workflow with updated stages list",
     *         @OA\JsonContent(ref="#/components/schemas/Workflow")
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
     * )
     */
    public function create(string $workflowId, Request $request): CreateStageResponse
    {
        $this->validate($request, [
            'name' => ['required'],
            'position' => ['int'],
        ]);

        $workflow = $this->workflowRepository->findById(new WorkflowId($workflowId));
        $this->requireModel($workflow, Workflow::class, [$workflowId]);

        /** @var User $user */
        $user = $request->user();
        /** @var CompanyAccountId $companyAccountId */
        $companyAccountId = $workflow->companyAccount()->uuid();
        $userCompanyAccount = $user->userCompanyAccount($companyAccountId);
        $this->requireModel($userCompanyAccount, CompanyAccount::class, [$companyAccountId]);

        Stage::createNew(
            $this->workflowRepository,
            $userCompanyAccount,
            $workflow,
            $request->get('name', ''),
            $request->get('position')
        );
        $this->workflowRepository->flush();

        return new CreateStageResponse($workflow);
    }
}
