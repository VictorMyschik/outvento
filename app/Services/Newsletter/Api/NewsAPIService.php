<?php

declare(strict_types=1);

namespace App\Services\Newsletter\Api;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Api\News\Request\NewsFilterRequest;
use App\Http\Controllers\Api\News\Response\NewsResponse;
use App\Http\Controllers\Api\Request\PaginationRequest;
use App\Services\Newsletter\NewsAPIInterface;

final readonly class NewsAPIService
{
    public function __construct(private NewsAPIInterface $newsAPI) {}

    public function listWithPaginate(PaginationRequest $paginationRequest, NewsFilterRequest $filterRequest): array
    {
        $page = $paginationRequest->getPage(0);
        $perPage = $paginationRequest->getPerPage(10);
        $sort = $paginationRequest->getSort('-updated_at');

        $paginator = $this->newsAPI->searchNews($filterRequest, $page, $perPage, $sort);

        $news = [];
        foreach ($paginator->items() as $item) {
            $news[] = $this->newsAPI->getNewsById($item->id, $filterRequest->getLanguage());
        }

        return [$news, $paginator];
    }

    public function getNewsById(int $newsId, LanguageEnum $language): ?NewsResponse
    {
        return $this->newsAPI->getNewsById($newsId, $language);
    }
}
