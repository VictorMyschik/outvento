<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Jobs\EmailJob;
use App\Mail\Feedback;
use App\Mail\NewsEmail;
use App\Mail\TravelInviteEmail;
use App\Models\Email\EmailLog;
use App\Repositories\System\SettingsRepositoryInterface;
use App\Services\Email\DTO\EmailDTO;
use App\Services\Email\Enum\EmailTypeEnum;
use App\Services\Forms\FormService;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

final readonly class EmailService
{
    public function __construct(
        private SubscriptionService         $service,
        private FormService                 $formService,
        private SettingsRepositoryInterface $settingsRepository,
    ) {}

    public function createNewEmail(EmailTypeEnum $type, mixed $form): void
    {
        if (!$this->settingsRepository->isEnabledEmailSend()) {
            return;
        }

        switch ($type) {
            case EmailTypeEnum::INVITE:
                $className = TravelInviteEmail::class;
                $dto = $form;
            case EmailTypeEnum::FEEDBACK:
                $className = Feedback::class;
                $emailDtos[] = new EmailDTO(
                    $this->settingsRepository->getAdminEmail(),
                    new $className(
                        $this->formService->getFormById($form->form->id()),
                        '')
                );
                break;
            case EmailTypeEnum::NEWS:
                $className = NewsEmail::class;
                $emailDtos = [];
                foreach ($this->service->getListTo($type, $form->form->getLanguage()) as $token => $email) {
                    $emailDtos[] = new EmailDTO($email, new $className($this->formService->getFormById($form->dto->id()), $token));
                }
                break;
        }

        foreach ($emailDtos as $dto) {
            EmailJob::dispatch($dto->to, $dto->mail, $type);
        }
    }

    public function send(string $to, Mailable $email, EmailTypeEnum $type): void
    {
        try {
            $result = Mail::to($to)->send($email->from(config('mail.from.address'), config('mail.from.name')));
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $log = new EmailLog();
        $log->setType($type);
        $log->setEmail($to);
        $log->setSubject($email->subject);
        $log->setEmailBody($email->render());
        $log->setStatus((bool)($result ?? null));
        $log->setError($error ?? null);
        $log->save();
    }
}
