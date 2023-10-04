<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNotification extends Notification
{
    use Queueable;

    public $module, $link, $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($module, $link, $message)
    {
        $this->module = $module;
        $this->message = $message;
        $this->link = $link;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'module'    =>  $this->module,
            'link'      =>  $this->link,
            'message'   =>  $this->message,
        ];
    }
}
