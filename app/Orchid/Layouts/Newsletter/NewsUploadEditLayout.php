<?php

namespace App\Orchid\Layouts\Newsletter;

use App\Models\Catalog\CatalogImage;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Rows;

class NewsUploadEditLayout extends Rows
{
    public function fields(): array
    {
        /** @var CatalogImage $image */
        $image = $this->query->get('image');

        if (!$image) {
            $out[] = Upload::make('news.logo')->groups('photo')->maxFiles(1)->path('news');
        } else {
            $out[] = Input::make('image.alt')->value($image?->getAlt())->title('alt')->type('text');
        }

        return $out;
    }
}
