<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Services\Telegram\Enum\ManageWords;

final readonly class TelegramService
{
    public function __construct(
        private Client $client,
    ) {}

    public function manageBot(string $user, string $message): void
    {
        $messageType = ManageWords::tryFromCode($message);

        match ($messageType) {
            ManageWords::START => $this->client->sendMessage($user, 'Hello! I am a bot! Send me a link of OLX site to the offer.'),
            ManageWords::HELP => $this->client->sendMessage($user, 'Commands: ' . $this->buildHelpMessage()),
            ManageWords::CLEAR => $this->clear($user),
            default => $this->importService->import(rawUrl: $message, user: $user),
        };
    }

    private function clear(string $user): void
    {
        $this->linkRepository->clearByUser($user);
        $this->client->sendMessage($user, 'Done!');
    }

    private function buildHelpMessage(): string
    {
        $str = '';

        foreach (ManageWords::getSelectList() as $key => $label) {
            $str .= $key . ' - ' . $label . "\n";
        }

        return $str;
    }

    public function sendRawMessage(string $user, string $message): void
    {
        $this->client->sendMessage($user, $message);
    }

    public function sendMessage(int $offerId, SiteType $type, array $userIds): void
    {
        $offer = $this->offerRepository->getOfferById($offerId);
        $message = $this->buildMessage($offer->getSl(), $type);

        foreach ($userIds as $userId) {
            $this->client->sendMessage($userId, $message);
        }
    }

    private function buildMessage(string $jsonData, SiteType $type): string
    {
        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);

        $extractor = new OfferExtractor($type, $data);

        $rows['Title:'] = $extractor->getTitle();
        $rows['Price:'] = $extractor->getPrice();

        foreach ($this->getParameters($type) as $param) {
            $rows[$param->getLabel()] = $extractor->getParameter($param->value);
        }

        $rows['URL:'] = $extractor->getLink();

        $out = '';
        foreach ($rows as $key => $item) {
            $out .= $key . ': ' . $item . "\n";
        }

        return $out;
    }
}
