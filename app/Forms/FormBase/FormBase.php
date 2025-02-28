<?php

declare(strict_types=1);

namespace App\Forms\FormBase;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class FormBase extends Controller
{
    protected const string TYPE_TEXT = 'textfield';
    protected const string TYPE_NUMBER = 'number';
    protected const string TYPE_SELECT = 'select';
    protected const string TYPE_CHECKBOX = 'checkbox';
    protected const string TYPE_RADIO = 'radio';
    protected const string TYPE_TEXTAREA = 'textarea';
    protected const string TYPE_DATE = 'date';
    protected const string TYPE_TIME = 'time';
    protected const string TYPE_DATETIME = 'datetime';

    protected array $v = [];
    protected array $errors = [];
    protected string $title = '';
    protected const string SIZE_50 = 'w-50';
    protected bool $btnInfo = false;

    public function processForm(): mixed
    {
        $method = request()->getMethod();

        if (in_array($method, ['GET', 'POST'])) {
            return $this->getFormBuilder();
        } elseif ('PUT' === $method) {
            $routeParameters = Route::getFacadeRoot()->current()->parameters();
            $this->validateFormBase($routeParameters);

            try {
                $this->submitForm($routeParameters);
            } catch (\Exception $e) {
                $this->errors[] = $e->getMessage();
                $this->returnError();
            }
        }

        return null;
    }

    public function getFormBuilder(): mixed
    {
        $routeParameters = Route::getFacadeRoot()->current()->parameters();

        $inputs = $this->builderForm($routeParameters);

        $formDisplay = View('form.base_form.new_form_templates')->with(['form' => $inputs])->toHtml();

        if (request()->getMethod() === 'POST') {
            return [
                'rendered'   => $formDisplay,
                'inputs' => $inputs,
                'system' => [
                    '#title'   => $this->title,
                    '#size'    => $this->size ?? 'w-50',
                    '#url'     => request()->url(),
                    '#btnInfo' => $this->btnInfo,
                ],
            ];
        } else { // GET запрос для дебага
            return View('form.base_form.form_render')->with([
                'form'      => $formDisplay,
                'form_data' => $inputs,
            ]);
        }
    }

    #region Validation

    /**
     * Базовая валидация на заполненность и тип данных
     */
    protected function validateFormBase(array $routeParameters = []): void
    {
        $fields = $this->getFormBuilder()['form_data'];

        // Get original form data.
        foreach ($fields as $fieldName => $parameters) {
            if (str_starts_with($fieldName, '#') || !isset($parameters['#type'])) {
                unset($fields[$fieldName]);
            }
        }

        foreach ($fields as $fieldName => $parameters) {
            // value from input
            $value = request()->get($fieldName, null);

            $title = $parameters['#title'] ?? '';

            if (array_key_exists('#required', $parameters) && true === $parameters['#required']) {
                if (empty(trim($value))) {
                    $this->errors[$fieldName] = 'Поле "' . $title . '" должно быть заполнено';
                }
            }

            $this->returnError();

            if ($value) {
                // Type numeric
                if (self::TYPE_NUMBER === $parameters['#type']) {
                    if (!is_numeric($value)) {
                        $this->errors[$fieldName] = 'Поле "' . $parameters['#title'] . '" имеет не верный формат';
                        $this->returnError();
                    }
                } elseif (self::TYPE_TEXT === $parameters['#type']) {
                    if (isset($parameters['#max']) && (int)$parameters['#max'] > strlen($value)) {
                        $this->errors[$fieldName] = 'Поле "' . $parameters['#title'] . '" ограничено ' . $parameters['#max'] . ' символами';
                    }
                }
            }

            $this->v[$fieldName] = $value;
        }

        $this->returnError();

        if (method_exists($this, 'validateForm')) {
            $this->validateForm($routeParameters);

            $this->returnError();
        }
    }

    private function returnError(): void
    {
        if (count($this->errors)) {
            Log::alert('Wrong set form', $this->errors);
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, json_encode($this->errors));
        }
    }
    #endregion
}
