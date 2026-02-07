<?php

namespace App\Notifications;

use App\Models\Certificate;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Course $course,
        public Certificate $certificate
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Congratulations! Course Completed - '.$this->course->title)
            ->greeting('Congratulations '.$notifiable->name.'!')
            ->line('You have successfully completed the course:')
            ->line('**'.$this->course->title.'**')
            ->line('Your certificate is ready! You can download it using the link below.')
            ->action('Download Certificate', url('/certificates/'.$this->certificate->certificate_number.'/download'))
            ->line('Certificate Number: '.$this->certificate->certificate_number)
            ->line('Keep up the great work and continue your learning journey!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'certificate_number' => $this->certificate->certificate_number,
        ];
    }
}
