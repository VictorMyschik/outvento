<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Response;

use App\Http\Controllers\API\User\Response\UserProfileResponse;
use App\Models\Upload\FileAttachment;
use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use stdClass;

final readonly class ResponseFactory
{
    public function getInviteResponse(array $data): array
    {
        $out = [];
        foreach ($data as $item) {
            $out[] = new InvitedUserComponent(
                email: $item['email'],
                createdAt: $item['created_at'],
            );
        }

        return $out;
    }

    public function getShopResponse(Shop $shop, ?User $user): ShopResponse
    {
        $user && $role = $shop->getRole($user)->getLabel();

        return new ShopResponse(
            id: $shop->id,
            title: $shop->title,
            role: $role ?? null,
            logo: $shop->getLogo(),
        );
    }

    public function getShopListResponse(array $list, User $user): array
    {
        $response = [];
        foreach ($list as $shop) {
            $response[] = $this->getShopResponse($shop, $user);
        }

        return $response;
    }

    public function getInvitationResponse(Shop $shop, Invitation $invitation): InvitationResponse
    {
        return new InvitationResponse(
            email: $invitation->email,
            shop: $this->getShopResponse($shop, null),
        );
    }

    private function getShopUserComponent(User $user, array $goodsCount): ShopUserComponent
    {
        return new ShopUserComponent(
            id: $user->id,
            active: $user->is_active,
            firstName: $user->first_name,
            lastName: $user->last_name,
            email: $user->email,
            phone: $user->phone,
            avatar: $user->getAvatar(),
            isOwner: ShopUserRoleEnum::from($user->role) === ShopUserRoleEnum::OWNER,
            isVerified: (bool)$user->email_verified_at,
            marketGoodComponent: new UserMarketGoodComponent(
                goodCount: $goodsCount['count'] ?? 0,
            ),
        );
    }

    public function getShopUsersList(array $list, array $userGoodsCount): array
    {
        $component = [];
        /** @var User $user */
        foreach ($list as $user) {
            $component[] = $this->getShopUserComponent($user, (array)($userGoodsCount[$user->id] ?? []));
        }

        return $component;
    }

    public function getMarketUserGroupResponse(MarketGroup $group): MarketUserGroupComponent
    {
        return $this->buildUserGroupComponent($group);
    }

    public function buildUserGroupComponent(MarketGroup $group): MarketUserGroupComponent
    {
        return new MarketUserGroupComponent(
            id: $group->id(),
            marketId: $group->market_id,
            title: $group->title
        );
    }

    public function getUserGroups(array $list): array
    {
        $response = [];
        foreach ($list as $item) {
            $response[] = $this->buildUserGroupComponent($item);
        }

        return $response;
    }

    public function getShopGoodList(Shop $shop, LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $markets = $shop->getMarkets();
        $users = $shop->users()->get()->keyBy('id');

        $paginator->through(function ($good) use ($markets, $users) {
            $component = new ShopGoodComponent(
                vendorCode: $good->vendor_code,
                title: $good->title,
                price: (float)$good->price,
                primeCost: (float)$good->prime_cost,
            );

            $data = (array)$good;

            foreach ($data as $property => $value) {
                $marketId = preg_replace('/^price_/', '', $property, 1, $count);

                if ($count === 0) {
                    continue;
                }

                $market = $markets[$marketId];
                $user = $users[$data['user_id_' . $marketId]] ?? null;

                if (!$value) {
                    continue;
                }

                $component->setMarketPrice(
                    new MarketGoodPriceComponent(
                        marketId: (int)$marketId,
                        type: $market->getType()->getCode(),
                        title: $market->title ?: $market->getType()->getLabel(),
                        price: (float)$value,
                        user: $user ? $this->getUserResponse($user) : null,
                        repricer: $data['repricer_' . $marketId] ?? false,
                        externalLink: MarketLinkHelper::getExternalLink((string)$good->externalid, $market->getType()),
                    )
                );
            }

            return $component;
        });

        return $paginator;
    }

    public function getWBGoodList(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->through(function ($good) {
            return new WBGoodComponent(
                id: $good->good_id,
                nmId: $good->nm_id,
                imtId: $good->imt_id,
                group: $good->group,
                vendorCode: $good->vendor_code,
                brand: $good->brand,
                title: $good->title,
                description: $good->description,
                image: $good->image,
            );
        });

        return $paginator;
    }

    public function getMarket(Market $market): MarketResponse
    {
        return new MarketResponse(id: $market->id(), active: $market->active, type: $market->getType()->getCode());
    }

    public function getMarkets(Shop $shop): array
    {
        $out = [];

        foreach ($shop->getMarkets() as $market) {
            $out[] = new MarketResponse(
                id: $market->id(),
                active: $market->active,
                type: $market->getType()->getCode(),
            );
        }

        return $out;
    }

    public function getUserResponse(User $user): UserProfileResponse
    {
        return new UserProfileResponse(
            id: $user->id,
            firstName: $user->first_name,
            lastName: $user->last_name,
            phone: $user->phone,
            email: $user->email,
            avatar: $user->getAvatar(),
            isVerified: (bool)$user->email_verified_at,
        );
    }

    public function getTariffResponse(Tariff $tariff, ?ShopTariff $shopTariff): ShopTariffResponse
    {
        return new ShopTariffResponse(
            title: $tariff->getTitle(),
            description: $tariff->getDescription(),
            isSubscription: $shopTariff?->isSubscription(),
            createdAt: $shopTariff?->created_at->toAtomString(),
            closedAt: $shopTariff?->closed_at->toAtomString(),
        );
    }

    public function getOrderResponse(Order $order): OrderResponse
    {
        return new OrderResponse(
            id: $order->id(),
            status: $order->getStatus()->getLabel(),
            shopTitle: $order->getShop()->getTitle(),
            amount: $order->getAmount(),
            currency: $order->getCurrency(),
            description: $order->getDescription(),
            createdAt: $order->created_at->format(DateTimeInterface::ATOM),
            updatedAt: $order->updated_at?->format(DateTimeInterface::ATOM),
            expirationDate: $order->getExpirationDate()->format(DateTimeInterface::ATOM),
        );
    }

    public function getPayment3dsResponse(PaymentResponse $response): ?Payment3dsResponse
    {
        if ($response instanceof ThreeDsPaymentResponse) {
            return new Payment3dsResponse(
                redirectUrl: $response->redirectUrl,
                parameters: array_map(
                    fn(TransactionParameter $component) => new PaymentResponseComponent(name: $component->name, value: $component->value),
                    $response->parameters,
                ),
            );
        }

        return null;
    }

    public function getTransactionResponse(Transaction $transaction): TransactionResponse
    {
        return new TransactionResponse(
            operationType: $transaction->getOperationType()->value,
            userId: $transaction->getUser()?->id(),
            amount: (float)$transaction->getMoney()->getAmount(),
            currency: $transaction->getMoney()->getCurrency()->getCurrencyCode(),
            status: $transaction->getStatus()->getLabel(),
            message: $transaction->getMessage(),
            isCaptured: $transaction->is_captured,
            created_at: $transaction->created_at->format(DateTimeInterface::ATOM),
            updated_at: $transaction->updated_at?->format(DateTimeInterface::ATOM),
        );
    }

    public function getTransactionList(array $list): array
    {
        $response = [];

        foreach ($list as $transaction) {
            $response[] = $this->getTransactionResponse($transaction);
        }

        return $response;
    }

    /**
     * @return MetricComponent[]
     */
    public function getMetricsResponse(FullMetricsDTO $dto, array $metricSources): array
    {
        $response = [];

        /** @var MetricTypeInterface $metric */
        foreach ($dto->data as $metric) {
            $response[] = new MetricComponent(
                type: $metric->getType()->getCode(),
                data: $this->buildNewDataItemComponents($metric->data),
                dateFrom: $metric->dateFrom,
                dateTo: $metric->dateTo,
                value: $metric->value,
                measure: $metric->getType()->getMeasure(),
                title: $metricSources[$metric->getType()->value]->name ?: $metric->getType()->getLabel(),
                description: $metricSources[$metric->getType()->value]->description ?: $metric->getType()->getDescription(),
            );
        }

        return $response;
    }


    private function buildNewDataItemComponents(array $items): array
    {
        $out = [];
        foreach ($items as $item) {
            $out[] = new DataItemComponent((string)$item['grouping'], (string)$item['value']);
        }
        return $out;
    }

    public function getTasksListResponse(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        return $paginator->through(function ($task) {
            return $this->getTaskResponse($task);
        });
    }

    public function getTaskResponse(Task $task): TaskResponse
    {
        $attachmentComponents = array_map([self::class, 'buildAttachmentComponent'], $task->getAttachmentList());
        $targetComponent = $this->getTargetResponseComponent($task);

        $parentTaskComponent = null;
        $parentTask = $task->getParentTask();

        if ($parentTask) {
            $parentTaskComponent = new ParentTaskComponent(
                $parentTask->id,
                $parentTask->external_id,
                $parentTask->title,
                $parentTask->deadline->toDateString(),
            );
        }

        $creatorComponent = $this->buildShortUserComponent($task->getUser());

        /** @var ?User $responsibleUser */
        $responsibleUser = $task->responsibleUser;
        $responsibleUserComponent = null;

        if ($responsibleUser) {
            $responsibleUserComponent = $this->buildShortUserComponent($responsibleUser);
        }

        /** @var ?User $closeUser */
        $closeUser = $task->getClosedUser();
        $closeUserComponent = null;

        if ($closeUser) {
            $closeUserComponent = $this->buildShortUserComponent($closeUser);
        }

        $commentComponents = [];
        foreach ($task->getComments() as $comment) {
            $commentComponents[] = $this->buildCommentComponent($comment);
        }

        return new TaskResponse(
            id: $task->id,
            externalId: $task->external_id,
            title: $task->title,
            description: $task->description,
            status: $task->status,
            deadline: $task->deadline,
            target: $targetComponent,
            parentTask: $parentTaskComponent,
            creator: $creatorComponent,
            responsibleUser: $responsibleUserComponent,
            closedUser: $closeUserComponent,
            attachments: $attachmentComponents,
            comments: $commentComponents,
            createdAt: $task->created_at,
            updatedAt: $task->updated_at,
        );
    }

    public function getTargetResponseComponent(Task $task): ?TargetComponentInterface
    {
        $target = $task->target;

        return match (true) {
            $target instanceof BaseGoodsEntity => $this->buildGoodTargetComponent($target),
            $target instanceof User => $this->getShopUserComponent($target, []),
            default => null,
        };
    }

    public function buildGoodTargetComponent(BaseGoodsEntity $entity): GoodTargetComponent
    {
        return new GoodTargetComponent(
            $entity->id(),
            $entity->getMorphClass(),
            $entity->getTitle(),
            $entity->getDescription(),
            $entity->getVendorCode(),
            $entity->getCreatedAt(),
        );
    }

    public function buildAttachmentComponent(FileAttachment $attachment): AttachmentComponent
    {
        return new AttachmentComponent(
            id: $attachment->id(),
            name: $attachment->getName(),
            url: $attachment->getUrl(),
        );
    }

    public function buildCommentComponent(Comment $comment, bool $withChildren = true): CommentComponent
    {
        return new CommentComponent(
            id: $comment->id,
            content: $comment->content,
            author: $this->getUserResponse($comment->getUser()),
            childrenComments: $withChildren ? $this->buildChildrenComment($comment) : [],
            attachments: array_map([self::class, 'buildAttachmentComponent'], $comment->getAttachments()),
            createdAt: $comment->created_at->format(DateTimeInterface::ATOM),
            updatedAt: $comment->updated_at?->format(DateTimeInterface::ATOM),
        );
    }

    public function buildChildrenComment(Comment $comment): array
    {
        $out = [];
        foreach ($comment->getChildren() as $child) {
            $out[] = $this->buildCommentComponent($child);
        }

        return $out;
    }

    /**
     * @param Comment[] $comments
     * @return TaskResponse[]
     */
    public function getCommentsListResponse(array $comments): array
    {
        return array_map([self::class, 'buildCommentComponent'], $comments);
    }

    public function getActionListResponse(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $metricService = AbstractMetricService::getByType(MarketTypeEnum::WILDBERRIES);
        $metricSource = $metricService->getMetricSources();

        return $paginator->through(function ($action) use ($metricSource) {
            $previousMetrics = $action->getPreviousMetrics();
            $actualMetrics = $action->getActualMetrics();

            return new ActionResponse(
                id: $action->id,
                types: $this->getActionTypeComponent($action->getTypes()),
                metrics: array_map(fn($metric) => MetricTypeEnum::fromCode($metric)->getLabel(), $action->getMetrics()),
                checkDate: $action->check_date->toDateString(),
                description: $action->description,
                creator: new UserComponent(
                    id: $action->user_id,
                    fullName: $action->user_full_name,
                    avatar: $action->user_avatar ? asset('storage' . $action->user_avatar) : null,
                ),
                previousMetrics: $previousMetrics ? $this->getMetricsResponse($previousMetrics, $metricSource) : null,
                actualMetrics: $actualMetrics ? $this->getMetricsResponse($actualMetrics, $metricSource) : null,
                createdAt: $action->created_at->format(DateTimeInterface::ATOM),
                updatedAt: $action->updated_at?->format(DateTimeInterface::ATOM),
            );
        });
    }

    public function buildShortUserComponent(User $user): UserComponent
    {
        return new UserComponent(
            $user->id,
            $user->getFullName(),
            $user->getAvatar(),
        );
    }

    public function getActionResponse(GoodAction $action): ActionResponse
    {
        $metricService = AbstractMetricService::getByType(MarketTypeEnum::WILDBERRIES);
        $metricSource = $metricService->getMetricSources();
        $previousMetrics = $action->getPreviousMetrics();
        $actualMetrics = $action->getActualMetrics();

        return new ActionResponse(
            id: $action->id,
            types: $this->getActionTypeComponent($action->getTypes()),
            metrics: $this->getMetricsTypeComponents($action->getMetrics()),
            checkDate: $action->check_date->toDateString(),
            description: $action->description,
            creator: $this->buildShortUserComponent($action->getUser()),
            previousMetrics: $previousMetrics ? $this->getMetricsResponse($previousMetrics, $metricSource) : null,
            actualMetrics: $actualMetrics ? $this->getMetricsResponse($actualMetrics, $metricSource) : null,
            createdAt: $action->created_at->format(DateTimeInterface::ATOM),
            updatedAt: $action->updated_at?->format(DateTimeInterface::ATOM),
        );
    }

    private function getActionTypeComponent(array $types): array
    {
        $out = [];
        foreach ($types as $type) {
            $out[$type] = GoodsActionTypeEnum::from($type)->getLabel();
        }

        return $out;
    }

    private function getMetricsTypeComponents(array $metrics): array
    {
        $out = [];
        foreach ($metrics as $metric) {
            $out[$metric] = MetricTypeEnum::fromCode($metric)->getLabel();
        }

        return $out;
    }

    public function getWBTokenInfo(MarketToken $marketToken, WBTokenHelperService $helper): WBTokenInfoResponse
    {
        $labels = array_map(
            fn(WBTokenServiceTypeEnum $service) => $service->getLabel(),
            $helper->getAllowedTypes($marketToken->getToken())
        );

        return new WBTokenInfoResponse(
            expired: $marketToken->getExpired()?->format(DATE_ATOM),
            created: $marketToken->created_at->format(DATE_ATOM),
            services: $labels,
        );
    }

    public function getShopUserFilterListResponse(array $list): array
    {
        $out = [];
        foreach ($list as $item) {
            $out[] = new ShopUserFilterComponent(
                id: $item->id(),
                title: $item->title,
                code: $item->code,
                createdAt: $item->created_at->format(DATE_ATOM),
                userId: $item->user_id,
                filterData: json_decode($item->filter, true),
            );
        }

        return $out;
    }

    public function getGoodAnalyticResponse(LengthAwarePaginator $paginator, array $types): LengthAwarePaginator
    {
        $paginator->through(function ($data) use ($types) {
            return new WBGoodAnalyticResponse(
                id: $data->id,
                title: $data->title,
                nmId: $data->nm_id,
                vendorCode: $data->vendor_code,
                brand: $data->brand,
                image: $data->image,
                groupName: $data->group_name,
                manager: trim($data->user_full_name) ?: null,
                metrics: $this->buildMetricsComponent($data, $types),
            );
        });

        return $paginator;
    }

    private function buildMetricsComponent(\stdClass $data, array $types): array
    {
        $out = [];
        /** @var MetricTypeEnum $type */
        foreach ($types as $type) {
            $out[] = new GoodAnalyticComponent(
                type: $type->getCode(),
                currentValue: (string)$data->{WBGoodAnalyticService::CURRENT_PREFIX . $type->getCode()},
                previousValue: (string)$data->{WBGoodAnalyticService::PREVIOUS_PREFIX . $type->getCode()},
                difference: (string)$data->{WBGoodAnalyticService::DIFF_PREFIX . $type->getCode()},
                measure: $type->getMeasure(),
                title: $type->getLabel(),
                description: $type->getDescription(),
            );
        }

        return $out;
    }

    public function buildGoodTimelineResponse(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->through(function ($data) {
            $type = TimelineEventEnum::from($data->type);

            $decoded = $data->getDecodedJsonFields();

            if (isset($decoded['subject_id'])) {
                $ids = [$decoded['subject_id']];
                if (isset($decoded['previous']['subject_id'])) {
                    $ids[] = $decoded['previous']['subject_id'];
                }

                $groupTitles = $this->catalogRepository->getGroupTitleListByIds($ids);

                $decoded['subject_id'] = $groupTitles[$decoded['subject_id']] ?? $decoded['subject_id'];
                if (isset($decoded['previous']['subject_id'])) {
                    $decoded['previous']['subject_id'] = $groupTitles[$decoded['previous']['subject_id']] ?? $decoded['previous']['subject_id'];
                }
            }

            return new TaskTimelineComponent(
                type: $type->getType(),
                typeCode: $type->getCode(),
                typeTitle: $type->getLabel(),
                user: $data->getUser() ? $this->buildShortUserComponent($data->getUser()) : null,
                data: match ($type->getType()) {
                    'task' => new ShortTaskComponent(
                        id: $decoded['id'],
                        title: $decoded['title'],
                        description: $decoded['description'],
                        status: $decoded['status'],
                        deadline: $decoded['deadline'] ? Carbon::parse($decoded['deadline'])->format(DateTimeInterface::ATOM) : null,
                    ),
                    'good_action' => $this->getActionResponse(new GoodAction()->forceFill($decoded)),
                    'good' => new WBShortGoodComponent(
                        id: $decoded['id'],
                        nmId: $decoded['nm_id'],
                        vendorCode: $decoded['vendor_code'],
                        title: $decoded['title'],
                        description: $decoded['description'] ?? null,
                        groupName: $decoded['subject_id'],
                        brandName: $decoded['brand'] ?? null,
                        previous: isset($decoded['previous']) ? new WBShortGoodComponent(
                            id: $decoded['previous']['id'],
                            nmId: $decoded['previous']['nm_id'],
                            vendorCode: $decoded['previous']['vendor_code'],
                            title: $decoded['previous']['title'],
                            description: $decoded['previous']['description'] ?? null,
                            groupName: $decoded['previous']['subject_id'],
                            brandName: $decoded['previous']['brand'],
                            previous: null,
                        ) : null,
                    ),
                    'good_price' => new WBShortGoodPriceComponent(
                        insideDiscount: (float)$decoded['inside_discount'],
                        discount: (int)$decoded['discount'],
                        finalPrice: (float)$decoded['final_price'],
                        price: (float)$decoded['price'],
                        clubDiscountedPrice: (float)$decoded['club_discounted_price'],
                        discountedPrice: (float)$decoded['discounted_price'],
                        sizeId: (int)$decoded['size_id'],
                        techSizeName: (string)$decoded['tech_size_name'],
                        previous: isset($decoded['previous']) ? new WBShortGoodPriceComponent(
                            insideDiscount: (float)$decoded['previous']['inside_discount'],
                            discount: (int)$decoded['previous']['discount'],
                            finalPrice: (float)$decoded['previous']['final_price'],
                            price: (float)$decoded['previous']['price'],
                            clubDiscountedPrice: (float)$decoded['previous']['club_discounted_price'],
                            discountedPrice: (float)$decoded['previous']['discounted_price'],
                            sizeId: (int)$decoded['previous']['size_id'],
                            techSizeName: (string)$decoded['previous']['tech_size_name'],
                            previous: null,
                        ) : null,
                    ),
                    'good_advert' => new WBGoodAdvertResponse(
                        advertId: $decoded['advert_id'],
                        advertName: $decoded['advert_name'],
                        status: AdvertStatusEnum::from($decoded['status'])->getLabel(),
                        endTime: $decoded['end_time'] ? Carbon::parse($decoded['end_time'])->format(DateTimeInterface::ATOM) : null,
                        createTime: Carbon::parse($decoded['create_time'])->format(DateTimeInterface::ATOM),
                        changeTime: Carbon::parse($decoded['change_time'])->format(DateTimeInterface::ATOM),
                        startTime: Carbon::parse($decoded['start_time'])->format(DateTimeInterface::ATOM),
                    ),
                },
                createdAt: $data->created_at->format(DateTimeInterface::ATOM),
                deletedAt: $data->deleted_at?->format(DateTimeInterface::ATOM),
            );
        });

        return $paginator;
    }

    public function buildTaskTimelineResponse(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->through(function ($data) {
            $type = TimelineEventEnum::from($data->type);

            $decoded = $data->getDecodedJsonFields();

            return new TaskTimelineComponent(
                type: $type->getType(),
                typeCode: $type->getCode(),
                typeTitle: $type->getLabel(),
                user: $this->buildShortUserComponent($data->getUser()),
                data: match ($type->getType()) {
                    'task' => new ShortTaskComponent(
                        id: $decoded['id'],
                        title: $decoded['title'],
                        description: $decoded['description'],
                        status: $decoded['status'],
                        deadline: $decoded['deadline'],
                    ),
                    'comment' => $this->buildCommentComponent(new Comment()->forceFill($decoded)),
                },
                createdAt: $data->created_at->format(DateTimeInterface::ATOM),
                deletedAt: $data->deleted_at?->format(DateTimeInterface::ATOM),
            );
        });

        return $paginator;
    }

    public function getPlanGoodsResponse(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->through(function ($data) {
            return new PlanGoodComponent(
                id: $data->id,
                nmId: $data->nm_id,
                title: $data->title ?: '',
                vendorCode: $data->vendor_code,
                groupName: $data->group_name,
                userName: $data->user_name,
                userId: $data->user_id,
                userAvatar: $data->user_avatar ? asset('storage' . $data->user_avatar) : null,
                amountPrevious: (int)$data->amount_previous,
                amountCurrent: (int)$data->amount_current,
                amountNeeded: (int)$data->amount_needed,
                sumPrevious: (float)$data->sum_previous,
                sumCurrent: (float)$data->sum_current,
                sumNeeded: (float)$data->sum_needed,
                warehousGoodAmount: (int)$data->warehouse_good_amount,
                price: $data->price ? (float)$data->price : null,
                percentCompleted: (float)$data->percent_completed,
            );
        });

        return $paginator;
    }

    public function getPlanGoodsSummaryComponent(stdClass $summaryData): PlanGoodSummaryComponent
    {
        return new PlanGoodSummaryComponent(
            totalSumPrevious: (float)$summaryData->total_sum_previous,
            totalSumCurrent: (float)$summaryData->total_sum_current,
            totalSumNeeded: (float)$summaryData->total_sum_needed,
            totalGoodsCount: (int)$summaryData->total_goods_count,
            percentCompleted: (float)$summaryData->percent_completed
        );
    }

    public function getPlanSummaryResponse(array $list, PlanSummaryRequest $request): PlanSummaryResponse
    {
        $items = [];
        $users = [];

        foreach ($list as $item) {
            $item = (array)$item;

            $items[] = $this->getPlanSummaryComponent($item);
            $users[] = [
                'userId'       => $item['user_id'],
                'userFullName' => $item['user_id'] ? $item['user_full_name'] : null,
                'userAvatar'   => $item['user_avatar'] ? asset('storage' . $item['user_avatar']) : null,
            ];
        }

        $totalSumCurrent = array_sum(array_column($list, 'total_sum_current'));
        $totalSumNeeded = array_sum(array_column($list, 'total_sum_needed'));

        return new PlanSummaryResponse(
            year: $request->getYear(),
            month: $request->getMonth(),
            totalSumPrevious: array_sum(array_column($list, 'total_sum_previous')),
            totalSumCurrent: round($totalSumCurrent, 2),
            totalSumNeeded: round($totalSumNeeded, 2),
            totalGoodsCount: array_sum(array_column($list, 'total_goods_count')),
            users: $users,
            percentCompleted: $totalSumNeeded > 0 ? round(($totalSumCurrent / $totalSumNeeded) * 100, 2) : 0,
            items: $items,
        );
    }

    public function getPlanSummaryComponent(array $item): PlanSummaryItemComponent
    {
        return new PlanSummaryItemComponent(
            percentCompleted: (float)$item['percent_completed'],
            totalSumPrevious: (float)$item['total_sum_previous'],
            totalSumCurrent: (float)$item['total_sum_current'],
            totalSumNeeded: (float)$item['total_sum_needed'],
            totalGoodsCount: (int)$item['total_goods_count'],
            userId: $item['user_id'],
            userFullName: $item['user_id'] ? $item['user_full_name'] : null,
            userAvatar: $item['user_avatar'] ? asset('storage' . $item['user_avatar']) : null,
            createdAt: Carbon::parse($item['created_at'])->toAtomString(),
        );
    }

    public function getPlanCalculateComponent(PlanSummaryDTO $dto): PlanCalculateComponent
    {
        return new PlanCalculateComponent(
            selectedGoodsCount: $dto->selectedGoodsCount,
            affectedGoodsCount: $dto->affectedGoodsCount,
            requestedSum: $dto->requestedSum,
            expectedSum: $dto->expectedSum,
            requestedPercent: $dto->requestedPercent,
            expectedPercent: $dto->expectedPercent,
            amountGoodsWithMinimalRequestedPlan: $dto->amountGoodsWithMinimalRequestedPlan,
            amountGoodsWithoutPrice: $dto->amountGoodsWithoutPrice,
            saved: $dto->saved,
            description: $dto->description,
        );
    }

    public function getManagersPerformanceResponse(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        return $paginator->through(function ($data) {
            return new ManagerPerformanceComponent(
                userId: $data->user_id,
                userFullName: trim($data->user_full_name),
                userAvatar: $data->user_avatar ? asset('storage' . $data->user_avatar) : null,
                totalGoodsCount: (int)$data->total_goods_count,
                ordersTotalCount: (int)$data->orders_total_count,
                previousOrdersTotalCount: (int)$data->previous_orders_total_count,
                revenueTotalSum: (float)$data->revenue_total_sum,
                previousRevenueTotalSum: (float)$data->previous_revenue_total_sum,
                totalOpenedTasksCount: (int)$data->total_opened_tasks_count,
                doneTotalTasksCount: (int)$data->done_total_tasks_count,
                previousDoneTotalTasksCount: (int)$data->previous_done_total_tasks_count,
                totalOverdueTasksCount: (int)$data->total_overdue_tasks_count,
                sumCapacity: (float)$data->sum_capacity,
            );
        });
    }
}
