<?php

declare(strict_types=1);

namespace App\Orchid\Fields;

use Orchid\Screen\Field;

class CKEditor extends Field
{
    /**
     * Blade template
     *
     * @var string
     */
    protected $view = 'fields.ckeditor';


    /**
     * Default attributes value.
     *
     * @var array
     */
    protected $attributes = [
        'options' => []
    ];

    /**
     * Attributes available for a particular tag.
     *
     * @var array
     */
    protected $inlineAttributes = [];

    public static function make(?string $name = null): static
    {
        return (new static())
            ->name($name)
            ->setOptions(config('ckeditor5.options', []));
    }

    public function setOptions(array $options): CKEditor
    {
        $this->attributes['options'] = $options;

        return $this;
    }

    public function mergeOptions(array $options): CKEditor
    {
        $this->attributes['options'] = array_merge($this->attributes['options'], $options);

        return $this;
    }
}
