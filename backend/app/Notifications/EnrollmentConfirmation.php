<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Course $course
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Enrollment Confirmation - '.$this->course->title)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Congratulations! You have successfully enrolled in the course:')
            ->line('**'.$this->course->title.'**')
            ->line('You can now start learning and access all course materials.')
            ->action('Start Learning', url('/learning/'.$this->course->id))
            ->line('Thank you for choosing our learning platform!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
        ];
    }
}
