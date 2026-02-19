<?php

declare(strict_types=1);

namespace App\Orchid\Screens\References;

use App\Models\Language;
use App\Models\LanguageName;
use App\Orchid\Filters\References\UserLanguageFilter;
use App\Orchid\Layouts\References\AddLanguageLayout;
use App\Orchid\Layouts\References\LanguageListLayout;
use App\Orchid\Layouts\References\LanguageNamesLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Symfony\Component\Intl\Languages;

class UserLanguageScreen extends Screen
{
    public string $name = 'Справочник доступных языков';
    public string $description = 'Используется для указания какими языками владеет пользователь';

    public function query(): iterable
    {
        return [
            'names' => [],
            'list'  => UserLanguageFilter::runQuery()->paginate(50),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Language')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('add_language_modal')
                ->method('addLanguage')
                ->modalTitle('Add language'),
        ];
    }

    public function layout(): iterable
    {
        return [
            UserLanguageFilter::displayFilterCard(request()),
            LanguageListLayout::class,
            Layout::modal('add_language_modal', AddLanguageLayout::class),
            Layout::modal('language_names_modal', LanguageNamesLayout::class)->async('asyncGetLanguageNames')->withoutApplyButton(),
        ];
    }

    public function addLanguage(Request $request): void
    {
        $languages = Languages::getNames('en');

        foreach ($languages as $code => $englishName) {

            // Фильтрация мусора CLDR (редко, но бывает)
            if (!is_string($code) || strlen($code) > 10) {
                continue;
            }

            /** @var Language $language */
            $language = Language::firstOrCreate([
                'code' => $code,
            ]);

            foreach ([$request->input('code')] as $locale) {

                $localizedName = Languages::getName($code, $locale);

                if (!$localizedName) {
                    continue;
                }

                LanguageName::updateOrCreate(
                    [
                        'language_id' => $language->id,
                        'locale'      => $locale,
                    ],
                    [
                        'name' => $localizedName,
                    ]
                );
            }
        }

    }

    public function asyncGetLanguageNames(int $languageId): array
    {
        $builder = Language::join(LanguageName::getTableName(), LanguageName::getTableName() . '.language_id', '=', Language::getTableName() . '.id')
            ->where(LanguageName::getTableName() . '.locale', app()->getLocale())
            ->select([
                'name as language_name',
                'code as language_code',
            ]);

        $data = Language::join(LanguageName::getTableName(), LanguageName::getTableName() . '.language_id', '=', Language::getTableName() . '.id')
            ->where(Language::getTableName() . '.id', $languageId)
            ->joinSub($builder, 'b', function ($q) {
                $q->on('b.language_code', '=', LanguageName::getTableName() . '.locale');
            })
            ->orderBy('language_name')
            ->get(['name', 'locale', 'language_name']);

        return [
            'names' => $data,
        ];
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (UserLanguageFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->input($item);
            }
        }

        return redirect()->route('reference.user.languages', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('reference.user.languages');
    }
    #endregion
}
