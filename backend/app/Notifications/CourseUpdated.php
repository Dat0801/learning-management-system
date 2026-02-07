<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Course $course,
        public array $updates = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Course Updated - '.$this->course->title)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('The course you are enrolled in has been updated:')
            ->line('**'.$this->course->title.'**');

        if (! empty($this->updates)) {
            $message->line('**Updates include:**');
            foreach ($this->updates as $update) {
                $message->line('- '.$update);
            }
        } else {
            $message->line('New content and improvements have been added to enhance your learning experience.');
        }

        return $message
            ->action('View Course', url('/courses/'.$this->course->id))
            ->line('Continue your learning journey!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'updates' => $this->updates,
        ];
    }
}
