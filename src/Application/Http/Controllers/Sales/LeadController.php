<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Responses\Sales\CloseLeadResponse;
use App\Http\Responses\Sales\CreateLeadResponse;
use App\Http\Responses\Sales\DeleteLeadResponse;
use App\Http\Responses\Sales\EditLeadResponse;
use App\Http\Responses\Sales\ReopenLeadResponse;
use Domains\Accounts\Models\Company\CompanyAccount;
use Domains\Accounts\Models\Company\CompanyAccountId;
use Domains\Accounts\Models\User\User;
use Domains\Accounts\Models\User\UserId;
use Domains\Accounts\Repositories\UserRepositoryContract;
use Domains\Common\Exceptions\DomainAuthException;
use Domains\Common\Exceptions\DomainException;
use Domains\Common\Exceptions\ValidationException;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionPerformedByContract;
use Domains\Sales\Models\Lead\Lead;
use Domains\Sales\Models\Lead\LeadId;
use Domains\Sales\Models\Permissions\ActionPermissions\ViewLead;
use Domains\Sales\Models\Revenue;
use Domains\Sales\Models\Workflow\Stage;
use Domains\Sales\Models\Workflow\StageId;
use Domains\Sales\Models\Workflow\Workflow;
use Domains\Sales\Models\Workflow\WorkflowId;
use Domains\Sales\Repositories\LeadRepositoryContract;
use Domains\Sales\Repositories\WorkflowRepositoryContract;
use Domains\Storage\Repositories\FileRepositoryContract;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class LeadController extends Controller
{
    private LeadRepositoryContract $leadRepository;
    private WorkflowRepositoryContract $workflowRepository;
    private UserRepositoryContract $userRepository;

    /**
     * @param LeadRepositoryContract     $leadRepository
     * @param WorkflowRepositoryContract $workflowRepository
     * @param UserRepositoryContract     $userRepository
     */
    public function __construct(
        LeadRepositoryContract $leadRepository,
        WorkflowRepositoryContract $workflowRepository,
        UserRepositoryContract $userRepository,
    )
    {
        $this->leadRepository = $leadRepository;
        $this->workflowRepository = $workflowRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Create new lead
     * @param string  $workflowId
     * @param Request $request
     * @return CreateLeadResponse
     * @throws \Domains\Common\Exceptions\DomainAuthException
     * @throws DomainException
     * @throws ValidationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @OA\Post (
     *     tags={"Lead", "Workflow"},
     *     path="/api/workflows/{workflowId}/leads",
     *      @OA\Header(
     *         header="Authorization",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="workflowId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="stage_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *         name="owner_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="planned_close_at",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="estimated_revenue",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Created lead data",
     *         @OA\JsonContent(ref="#/components/schemas/Lead")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. Missed some query parameters or validation failed."
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication failed. Token sent via email is required in header."
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
    public function create(string $workflowId, Request $request): CreateLeadResponse
    {
        $this->validate(
            $request,
            [
                'title' => 'required',
                'stage_id' => 'required',
                'owner_id' => 'required',
                'planned_close_at' => ['int', 'required'],
                'estimated_revenue' => ['numeric', 'max:'.Revenue::MAX_VALUE, 'nullable']
            ]
        );

        $workflow = $this->workflowRepository->findById(new WorkflowId($workflowId));
        $this->requireModel($workflow, Workflow::class, [$workflowId]);

        $sageId = $request->get('stage_id', '');
        $stage = $workflow->findStage(new StageId($sageId));
        $this->requireModel($stage, Stage::class, [$workflowId, $sageId]);

        /** @var User $user */
        $user = $request->user();
        /** @var CompanyAccountId $companyAccountId */
        $companyAccountId = $workflow->companyAccount()->uuid();
        /** @var ActionPerformedByContract $userCompanyAccount */
        $userCompanyAccount = $user->userCompanyAccount($companyAccountId);
        $this->requireModel($userCompanyAccount, CompanyAccount::class, [$companyAccountId]);

        /** @var CompanyAccount $companyAccount */
        $companyAccount = $userCompanyAccount->companyAccount();
        $ownerId = $request->get('owner_id', '');
        $owner = $companyAccount->findMember(new UserId($ownerId));
        $this->requireModel($owner, User::class, [$ownerId]);


        $revenue = $request->get('estimated_revenue');
        $plannedCloseAt = (new \DateTimeImmutable())->setTimestamp($request->get('planned_close_at'));

        $lead = Lead::createNewOne(
            $this->workflowRepository,
            $request->get('title', ''),
            $stage,
            $owner->user(),
            $userCompanyAccount,
            $revenue ? Revenue::fromDecimal((float)$revenue) : null,
            $plannedCloseAt,
        );
        $this->workflowRepository->flush();

        return new CreateLeadResponse($lead);
    }

    /**
     * Delete lead
     * @param string  $workflowId
     * @param string  $leadId
     * @param Request $request
     * @return DeleteLeadResponse
     * @throws \Domains\Common\Exceptions\DomainAuthException
     * @throws DomainException
     *
     * @OA\Delete (
     *     tags={"Lead", "Workflow"},
     *     path="/api/workflows/{workflowId}/leads/{leadId}",
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
     *       name="leadId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *      @OA\Response(
     *         response="204",
     *         description="Deleted lead"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. Missed some query parameters or validation failed."
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
    public function delete(string $workflowId, string $leadId, Request $request): DeleteLeadResponse
    {
        /** @var User $user */
        $user = $request->user();
        $lead = $this->leadRepository->findById(new LeadId($leadId));
        $this->requireModel($lead, Lead::class, [$leadId]);

        /** @var CompanyAccountId $companyAccountId */
        $companyAccountId = $lead->companyAccount()->uuid();
        /** @var ActionPerformedByContract $userCompanyAccount */
        $userCompanyAccount = $user->userCompanyAccount($companyAccountId);
        $this->requireModel($userCompanyAccount, CompanyAccount::class, [$companyAccountId]);

        $lead->delete($this->workflowRepository, $userCompanyAccount);
        $this->workflowRepository->flush();

        return new DeleteLeadResponse();
    }

    /**
     * Edit lead
     * @param string  $workflowId
     * @param string  $leadId
     * @param Request $request
     * @return EditLeadResponse
     * @throws \Domains\Common\Exceptions\DomainAuthException
     * @throws ValidationException
     * @throws \Exception
     *
     * @OA\Patch (
     *     tags={"Lead", "Workflow"},
     *     path="/api/workflows/{workflowId}/leads/{leadId}",
     *      @OA\Header(
     *         header="Authorization",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="workflowId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="leadId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *         name="owner_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="estimated_revenue",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="planned_close_at",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(
     *         response="202",
     *         description="Updated lead",
     *         @OA\JsonContent(ref="#/components/schemas/Lead")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. Missed some query parameters or validation failed."
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
    public function edit(string $workflowId, string $leadId, Request $request): EditLeadResponse
    {
        $this->validate(
            $request,
            [
                'title' => 'string',
                'estimated_revenue' => ['numeric', 'max:'.Revenue::MAX_VALUE, 'nullable'],
                'planned_close_at' => 'int',
            ]
        );

        /** @var User $user */
        $user = $request->user();
        /** @var Lead $lead */
        $lead = $this->leadRepository->findById(new LeadId($leadId));
        $this->requireModel($lead, Lead::class, [$leadId]);

        /** @var CompanyAccountId $companyAccountId */
        $companyAccountId = $lead->companyAccount()->uuid();
        /** @var ActionPerformedByContract $userCompanyAccount */
        $userCompanyAccount = $user->userCompanyAccount($companyAccountId);
        $this->requireModel($userCompanyAccount, CompanyAccount::class, [$companyAccountId]);

        $owner = $lead->owner();
        if($request->has('owner_id')) {
            $member = $lead->companyAccount()->findMember(new UserId($request->get('owner_id')));
            $this->requireModel($member, User::class, [$request->get('owner_id')]);
            $owner = $member->user();
        }

        $title = $request->has('title') ? $request->get('title') : $lead->title();
        $revenue = $request->has('estimated_revenue')
            ? $request->get('estimated_revenue')
            : $lead->estimatedRevenue()?->value(true);
        $plannedCloseAt = $request->has('planned_close_at')
            ? $request->get('planned_close_at')
            : $lead->plannedCloseAt()->getTimestamp();

        $lead->edit(
            $this->workflowRepository,
            $userCompanyAccount,
            $owner,
            $title,
            $revenue ? Revenue::fromDecimal((float)$revenue) : null,
            (new \DateTimeImmutable())->setTimestamp($plannedCloseAt),
        );
        $this->workflowRepository->flush();

        return new EditLeadResponse($lead);
    }

    /**
     * Close lead
     * @param string  $leadId
     * @param Request $request
     * @return CloseLeadResponse
     * @throws DomainAuthException
     * @throws DomainException
     * @throws ValidationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @OA\Patch (
     *     tags={"Lead", "Workflow"},
     *     path="/api/leads/{leadId}/close",
     *      @OA\Header(
     *         header="Authorization",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="leadId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="closed_at",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Parameter(
     *         name="actual_revenue",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="number", format="double")
     *     ),
     *     @OA\Response(
     *         response="202",
     *         description="Closed lead",
     *         @OA\JsonContent(ref="#/components/schemas/Lead")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. Missed some query parameters or validation failed."
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
    public function close(string $leadId, Request $request): CloseLeadResponse
    {
        $this->validate($request, [
            'closed_at' => ['int', 'required'],
            'actual_revenue' => ['numeric'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $lead = $this->leadRepository->findById(new LeadId($leadId));
        $this->requireModel($lead, Lead::class, [$leadId]);

        /** @var CompanyAccountId $companyAccountId */
        $companyAccountId = $lead->companyAccount()->uuid();
        /** @var ActionPerformedByContract $userCompanyAccount */
        $userCompanyAccount = $user->userCompanyAccount($companyAccountId);
        $this->requireModel($userCompanyAccount, CompanyAccount::class, [$companyAccountId]);

        $actualRevenue = $request->has('actual_revenue') ?  Revenue::fromDecimal((float)$request->get('actual_revenue')) : null;
        $lead->close(
            $this->workflowRepository,
            $userCompanyAccount,
            new \DateTimeImmutable('@' . (int)$request->get('closed_at')),
            $actualRevenue
        );
        $this->workflowRepository->flush();

        return new CloseLeadResponse($lead);
    }

    /**
     * Reopen closed lead
     * @param string  $leadId
     * @param Request $request
     * @return ReopenLeadResponse
     * @throws DomainAuthException
     * @throws DomainException
     *
     * @OA\Patch (
     *     tags={"Lead", "Workflow"},
     *     path="/api/leads/{leadId}/reopen",
     *      @OA\Header(
     *         header="Authorization",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="leadId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="202",
     *         description="Reopened lead",
     *         @OA\JsonContent(ref="#/components/schemas/Lead")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. Missed some query parameters or validation failed."
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
    public function reopen(string $leadId, Request $request): ReopenLeadResponse
    {
        /** @var User $user */
        $user = $request->user();
        $lead = $this->leadRepository->findById(new LeadId($leadId));
        $this->requireModel($lead, Lead::class, [$leadId]);

        /** @var CompanyAccountId $companyAccountId */
        $companyAccountId = $lead->companyAccount()->uuid();
        /** @var ActionPerformedByContract $userCompanyAccount */
        $userCompanyAccount = $user->userCompanyAccount($companyAccountId);
        $this->requireModel($userCompanyAccount, CompanyAccount::class, [$companyAccountId]);

        $lead->reopen(
            $this->workflowRepository,
            $userCompanyAccount
        );
        $this->workflowRepository->flush();

        return new ReopenLeadResponse($lead);
    }
}
