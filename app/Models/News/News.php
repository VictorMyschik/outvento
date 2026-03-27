<?php

declare(strict_types=1);

namespace App\Models\News;

use App\Models\Lego\Fields\ActiveFieldTrait;
use App\Models\Lego\Fields\CodeFieldTrait;
use App\Models\Lego\Fields\LanguageFieldTrait;
use App\Models\Lego\Fields\TitleFieldTrait;
use App\Models\ORM\ORM;
use App\Services\Newsletter\ImageUploader\Enum\NewsMediaType;
use Carbon\Carbon;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class News extends ORM
{
    use AsSource;
    use Filterable;
    use CodeFieldTrait;
    use TitleFieldTrait;
    use ActiveFieldTrait;
    use LanguageFieldTrait;

    protected $table = 'news';

    protected array $allowedSorts = [
        'id',
        'active',
        'group_id',
        'public',
        'title',
        'published_at',
        'language',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'published_at' => 'datetime',
    ];

    public function isPublic(): bool
    {
        return (bool)$this->public;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getGroupId(): ?int
    {
        return $this->group_id;
    }

    public function getPublishedAt(): ?Carbon
    {
        return $this->published_at;
    }

    public function getTextVisible(array $uriList): string
    {
        $links = [];
        foreach ($uriList as $linkItem) {
            $links[] = '<a href="' . config('services.front.host') . $linkItem . '" target="_blank"><b>ссылке</b></a>';
        }

        if ($this->isActive() && $this->isPublic()) {
            return 'Новость участвует в поиске и доступна по прямой ссылке ' . implode(', ', $links);
        }

        if (!$this->isActive() && $this->isPublic()) {
            return 'Новость доступна только по прямой ссылке ' .implode(', ', $links);
        }

        return 'Новость не участвует в поиске и не доступна по прямой ссылке ' .implode(', ', $links);
    }

    /**
     * @return NewsSubgroup[]
     */
    public function getSubgroupList(): array
    {
        return NewsSubgroup::join(NewsInSubgroup::getTableName(), NewsSubgroup::getTableName() . '.id', '=', NewsInSubgroup::getTableName() . '.subgroup_id')
            ->where(NewsInSubgroup::getTableName() . '.news_id', $this->id())
            ->get(NewsSubgroup::getTableName() . '.*')
            ->all();
    }

    public function getLogo(): ?NewsMedia
    {
        return NewsMedia::where('news_id', $this->id())->where('media_type', NewsMediaType::Logo->value)->first();
    }

    public function getGroup(): NewsGroup
    {
        return NewsGroup::loadByOrDie($this->getGroupId());
    }

    public function getUrl(): string
    {
        return $this->getUriList()[0];
    }

    public function getUriList(): array
    {
        $groupCode = $this->getGroup()->code;
        $subgroups = $this->getSubgroupList();
        $out = [];

        if (count($subgroups) > 0) {
            foreach ($subgroups as $subgroup) {
                $out[] = '/' . $groupCode . '/' . $subgroup->code . '/news/' . $this->code;
            }
        } else {
            $out[] = '/' . $groupCode . '/news/' . $this->code;
        }

        return $out;
    }
}
