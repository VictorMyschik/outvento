<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Travel\Request;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'country'       => 'nullable|int|exists:countries,id',
            'activity'      => 'nullable|int|exists:activity,id',
            'dateFrom'      => 'nullable|date',
            'dateTo'        => 'nullable|date',
            'maxMemberFrom' => 'nullable|int',
            'maxMemberTo'   => 'nullable|int',
            'freeMember'    => 'nullable|int',
        ];
    }
}
