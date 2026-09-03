<?php

namespace App\Notifications;

use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PatientRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(public Patient $patient) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{kind: string, title: string, message: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'patient.registered',
            'title' => __('New patient registered'),
            'message' => __(':name has been registered.', ['name' => $this->patient->name]),
            'url' => route('patients.show', $this->patient, false),
        ];
    }
}
