<?php

namespace App\Scheduler;

use App\Message\SendRemindersMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('default')]
class ReminderScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                // Planifie l'envoi du message chaque 1 minute
                RecurringMessage::every('1 minute', new SendRemindersMessage())
            );
    }
}
