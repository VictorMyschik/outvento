<?php

declare(strict_types=1);

namespace App\Services\Catalog\Onliner;

use App\Jobs\Catalog\DownloadGoodJob;
use App\Jobs\Catalog\SearchGoodsByCatalogGroupJob;
use App\Models\Catalog\CatalogGood;
use App\Models\Catalog\CatalogGroup;
use App\Services\Catalog\CatalogService;
use App\Services\Catalog\Onliner\API\OnlinerClient;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

final class ImportOnlinerService
{
    public function __construct(
        private readonly OnlinerClient          $client,
        private readonly CatalogService         $catalogService,
        private readonly ImageUploaderInterface $imageService
    ) {}

    public function import(string $stringId, CatalogGroup $group, string $url, bool $isLoadImages): int
    {
        $cleanData = $this->client->doGet($url);

        $parsedData = $this->parse((string)$cleanData);
        $parsedData['string_id'] = $stringId;

        return $this->createGoodWithAllInfo($group, $parsedData, $url, (string)$cleanData, $isLoadImages);
    }

    private function createGoodWithAllInfo(CatalogGroup $group, array $parsedData, string $url, string $cleanData, bool $isLoadImages = true): int
    {
        $goodId = $this->catalogService->saveGood(0, [
            'group_id'  => $group->id(),
            'name'      => $parsedData['good_name'],
            'string_id' => $parsedData['string_id'],
            'link'      => $url,
        ]);

        $attributes = $this->createCatalogAttributes($parsedData['attributes'], $group);

        $this->insertGoodAttributes($goodId, $attributes);

        $this->updateGoodWithManufacturer($goodId, $parsedData['string_id']);

        if ($isLoadImages) {
            // TODO: пока не перекачиваем, только сохраняем инфу в БД
            /*foreach ($parsedData['images'] as $imageUrl) {
                $this->imageService->uploadImageByURL($goodId, $imageUrl);
            }*/

            $this->imageService->setBulkImages($goodId, $parsedData['images']);
        }

        Log::info('Создан товар: ' . $parsedData['good_name'] . '. ID' . $goodId);

       // event(new ESAddGoodEvent($this->catalogService->getGoodById($goodId)));
        return $goodId;
    }

    public function importOnlinerImagesCatalog(int $goodId, string $htmlData): void
    {
        $crawler = new Crawler($htmlData);

        $imageNames = $this->parseImgUrls($crawler);

        foreach ($imageNames as $imageUrl) {
            $this->imageService->uploadImageByURL($goodId, $imageUrl);
        }
    }

    private function parseImgUrls(Crawler $crawler): array
    {
        $scripts = $crawler->filter('script')->each(function (Crawler $node) {
            if (str_contains($node->text(), 'window.__NUXT__') && str_contains($node->text(), 'imgproxy')) {
                return $node->text();
            }

            return null;
        });

        $scripts = array_filter($scripts);
        $scriptText = reset($scripts);

        $url_pattern = '/(\w+):\s*"(https:\\\\u002F\\\\u002F[^"]+)"/';

        preg_match_all($url_pattern, $scriptText, $matches, PREG_SET_ORDER);

        $result = [];
        foreach ($matches as $match) {
            $key = $match[1]; // main, retina, or thumbnail
            $url = json_decode('"' . $match[2] . '"');
            if ($key === 'retina') {
                $result[] = $url;
            }
        }

        return $result;
    }

    public function reloadGoodImages(CatalogGood $good): void
    {
        $url = $good->getJsonField('html_url');

        $cleanData = $this->client->doGet($url);

        $this->catalogService->deleteAllGoodPhoto($good->id());
        $this->importOnlinerImagesCatalog($good->id(), (string)$cleanData);
    }

    private function updateGoodWithManufacturer(int $goodId, string $stringId): void
    {
        $jsonData = $this->client->doGet("https://catalog.onliner.by/sdapi/catalog.api/products/$stringId");
        $data = json_decode((string)$jsonData, true);

        $dataForUpdate['int_id'] = $data['id'] ?? null;

        if ($manufacturerJson = $data['manufacturer'] ?? null) {
            $manufacturer = $this->catalogService->getManufacturerOrCreateNew([
                'name'    => $manufacturerJson['name'],
                'address' => $manufacturerJson['legal_address']
            ]);

            $dataForUpdate['manufacturer_id'] = $manufacturer->id();
        }

        $dataForUpdate['name'] = $data['name'] ?? null;

        if ($data['name_prefix'] ?? null) {
            $dataForUpdate['prefix'] = $data['name_prefix'];
        }

        $dataForUpdate['is_certification'] = $data['certification_required'] ?? false;
        $dataForUpdate['parent_good_id'] = $data['parent_key'] ?? null;
        $dataForUpdate['description'] = $data['description'] ?? null;
        $dataForUpdate['sl'] = $jsonData;

        $this->catalogService->saveGood($goodId, $dataForUpdate);
    }

    private function insertGoodAttributes(int $goodId, array $attributes = array()): void
    {
        $newGoodAttributes = array();

        foreach ($attributes as $attribute) {
            $newGoodAttributes[] = array(
                'good_id'            => $goodId,
                'bool_value'         => is_null($attribute['bool']) ? null : (bool)$attribute['bool'],
                'attribute_value_id' => $attribute['attr_val'],
            );
        }

        if (count($newGoodAttributes)) {
            $this->catalogService->createGoodAttributes($newGoodAttributes);
        }
    }

    private function createCatalogAttributes(array $data, CatalogGroup $catalogGroup): array
    {
        $out = [];

        foreach ($data as $groupName => $subGroup) {
            $sortOrder = 1000;
            $group = $this->catalogService->getGroupAttributeOrCreateNew($catalogGroup->id(), $groupName, $sortOrder);

            foreach ($subGroup as $title => $value) {
                $catalogAttribute = $this->catalogService->getCatalogAttributeOrCreateNew($group, $title);

                $this->catalogService->getCatalogAttributeValueOrCreateNew($catalogAttribute, null);
                $catalogAttributeValue = $this->catalogService->getCatalogAttributeValueOrCreateNew($catalogAttribute, $value['text_value'] ?: null);

                $out[] = [
                    'bool'     => $value['bool'],
                    'attr_val' => $catalogAttributeValue->id(),
                ];
            }
        }

        return $out;
    }

    private function parse($data): array
    {
        $crawler = new Crawler($data);
        $data = $crawler->filter('table')->filter('tr')->each(function ($tr) {
            return $tr->filter('td')->each(function ($td, $i) {
                if ($i === 0) { // наименование атрибута
                    $tmp = trim($td->text($td->html(), false));
                    $tmp = explode("\n", $tmp)[0] ?? null;

                    $out = $tmp;
                } else {// Значение атрибута
                    $class = null;

                    if (!empty($td->filter('span'))) {
                        if (!empty($td->filter('span')->extract(['class']))) {
                            $class = $td->filter('span')->extract(['class'])[0] ?? null;
                        }
                    }

                    $out = array(
                        'i'     => $class,
                        'value' => $td->filter('.value__text')->text($td->text(), false));
                }

                return $out;
            });
        });

        $attrFormatted = array();
        $title = '';
        // Удаление лишних разделов
        foreach ($data as $tmp_key => $tmp_item) {
            if ($tmp_item[0] === 'Описание') {
                unset($data[$tmp_key]);
            }
        }

        foreach ($data as $item) {
            if (count($item) === 1) {
                $title = $item[0];
                $attrFormatted[$item[0]] = array();
            } else {
                $tmpValue = null;
                $boolValue = null;
                if ($item[1]['i'] === 'i-x') {
                    $boolValue = false;
                } elseif ($item[1]['i'] === 'i-tip') {
                    $boolValue = true;
                }

                $tmpValue = $item[1]['value'] ?? null;

                $attrFormatted[$title][$item[0]] = array(
                    'bool'       => $boolValue,
                    'text_value' => strlen(trim($tmpValue)) ? trim($tmpValue) : null,
                );
            }
        }

        $out['good_name'] = $crawler->filter('.catalog-masthead__title')->text();
        $out['attributes'] = $attrFormatted;
        $out['images'] = $this->parseImgUrls($crawler);

        return $out;
    }

    private array $urls = [];

    public function parseUrlList(string $url, int $max = 0): array
    {
        if ($max && count($this->urls) > $max) {
            return $this->urls;
        }

        $json = json_decode((string)$this->client->doGet($url), true);

        if (isset($json['products']) && count($json['products'])) {
            foreach ($json['products'] as $product) {
                $this->urls[] = $product['html_url'];
            }

            $pageNumber = explode('=', $url);
            $newPageNumber = $pageNumber[0] . '=' . (++$pageNumber[1]);

            $this->parseUrlList($newPageNumber);
        }

        return $this->urls;
    }

    public function updateCatalogGoods(): void
    {
        $list = $this->catalogService->getCatalogGroupList();

        foreach ($list as $group) {
            SearchGoodsByCatalogGroupJob::dispatch($group->id());
        }
    }

    /**
     * @throws Exception
     */
    public function searchNewGoodsByCatalogGroup(CatalogGroup $group): void
    {
        if (!$group->getJsonLink()) {
            throw new Exception('Поиск и добавление новых товаров в каталог: ' . $group->getName() . ' - нет ссылки на json');
        }

        if (!$article = $group->getOnlinerArticleName()) {
            throw new Exception('Поиск и добавление новых товаров в каталог: ' . $group->getName() . ' - нет артикула');
        }

        $link = "https://catalog.onliner.by/sdapi/catalog.api/search/$article?page=1";

        // Получение списка страниц
        $json = (string)$this->client->doGet($link);
        $data = @json_decode($json, true);

        // количество всего страниц
        if ($data['page'] ?? null) {
            $cntPage = $data['page']['last'] ?? 2;

            // Создание задач. Одна страница - одна задача
            for ($i = 1; $i <= $cntPage; $i++) {
                $link = "https://catalog.onliner.by/sdapi/catalog.api/search/$article?page=$i";
                DownloadGoodJob::dispatch($group, $link);
            }
        }
    }

    public function downloadGoods(CatalogGroup $group, string $link): void
    {
        $data = (string)$this->client->doGet($link);
        $json = @json_decode($data, true);

        if (isset($json['products']) && count($json['products'])) {
            foreach ($json['products'] as $product) {
                // Постановка в очередь проверки и скачки новых товаров
                if ($this->catalogService->hasGoodByStringId((string)$product['key'])) {
                    Log::warning($group->getName() . ' int_id ' . (int)$product['id'] . " " . $product['name'] . ' уже есть в базе');
                    continue;
                }

                if ($product['html_url'] ?? null) {
                    // Найден новый товар - постановка задачи на скачивание
                    $this->import(stringId: $product['key'], group: $group, url: (string)$product['html_url'], isLoadImages: true);
                } else {
                    Log::info('Новый товар ' . $product['id'] . " без HTML ссылки", [
                        'product' => $product,
                        'group'   => $group->getName(),
                        'link'    => $link,
                    ]);
                }
            }
        } else {
            Log::info($group->getName() . " не нашёл товаров вообще", ['json' => $json, 'link' => $link]);
        }
    }
}
