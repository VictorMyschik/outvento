<?php

declare(strict_types=1);

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Forms\Request\FeedbackRequest;
use App\Http\Controllers\Forms\Request\FormRequestInterface;
use App\Services\Forms\FormService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FormsController extends Controller
{
    public function __construct(private readonly FormService $service) {}

    public function feedback(FeedbackRequest $request): void
    {
        $this->run($request);
    }

    private function run(FormRequestInterface $request): void
    {
        try {
            $this->service->addForm($request->getDto($this->getLanguage()));
        } catch (Throwable $e) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }
}
