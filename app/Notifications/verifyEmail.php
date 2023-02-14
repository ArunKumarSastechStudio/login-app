<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class verifyEmail extends Notification
{
    use Queueable;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    // public $tries = 3; // Max tries
    public function __construct($token)
    {
        $this->token = $token;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $title = 'Email Confirmation';

        $data["lines"] = [
            "Hello " . $title . ",",
            "We received a password forgot request for your account. To reset your password, please click on the link below.",
        ];
        $data["button"] = [
            "name" => "Reset Password",
            "value" => config('customconfig.APP_UI_URL') . '/reset-password' . '?token=' . trim($this->token['token']),
        ];
        return (new MailMessage)
            ->from('arunkumardhangar2786@gmail.com', $title)
            ->subject('Email Confirmation')
            ->markdown('mails.reset_password', ["data" => $data]);
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
            //
        ];
    }
}
