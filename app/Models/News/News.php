<?php

declare(strict_types=1);

namespace App\Models\News;

use App\Models\Lego\Fields\ActiveFieldTrait;
use App\Models\Lego\Fields\CodeFieldTrait;
use App\Models\Lego\Fields\TitleFieldTrait;
use App\Models\ORM\ORM;
use App\Services\Newsletter\ImageUploader\Enum\NewsMediaType;
use App\Services\System\Enum\Language;
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

    public function getLanguage(): Language
    {
        return Language::from($this->language);
    }

    public function getPublishedAt(): ?Carbon
    {
        return $this->published_at;
    }

    public function getTextVisible(string $link): string
    {
        if ($this->isActive() && $this->isPublic()) {
            return 'Новость участвует в поиске и доступна по прямой ссылке ' . $link;
        }

        if (!$this->isActive() && $this->isPublic()) {
            return 'Новость доступна только по прямой ссылке ' . $link;
        }

        return 'Новость не участвует в поиске и не доступна по прямой ссылке ' . $link;
    }

    /**
     * @return NewsSubgroup[]
     */
    public function getSubgroupList(): array
    {
        return NewsSubgroup::join(NewsInSubgroup::getTableName(), NewsSubgroup::getTableName() . '.id', '=', NewsInSubgroup::getTableName() . '.subgroup_id')
            ->where(NewsInSubgroup::getTableName() . '.news_id', $this->id())
            ->get()
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
        return env('FRONT_HOST') . "/news/{$this->getGroup()->getCode()}/{$this->getCode()}";
    }
}
