<?php

namespace App\MessageHandler;

use App\Message\SendRemindersMessage;
use App\Service\ReminderService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendRemindersMessageHandler
{
    public function __construct(
        private ReminderService $reminderService
    ) {}

    public function __invoke(SendRemindersMessage $message)
    {
        // On appelle le service pour envoyer les rappels
        $this->reminderService->sendDailyReminders();
    }
}
