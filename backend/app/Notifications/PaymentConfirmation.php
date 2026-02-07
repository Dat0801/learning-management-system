<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Transaction $transaction,
        public Course $course
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Confirmation - '.$this->course->title)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your payment has been successfully processed.')
            ->line('**Course:** '.$this->course->title)
            ->line('**Amount:** $'.number_format($this->transaction->amount, 2))
            ->line('**Transaction ID:** '.$this->transaction->transaction_id)
            ->line('**Payment Date:** '.$this->transaction->paid_at->format('F d, Y'))
            ->action('Access Course', url('/learning/'.$this->course->id))
            ->line('Thank you for your purchase!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->transaction_id,
            'course_id' => $this->course->id,
            'amount' => $this->transaction->amount,
        ];
    }
}
