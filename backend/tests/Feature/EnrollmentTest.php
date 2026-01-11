<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_enroll_in_course()
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($student)
                         ->postJson("/api/courses/{$course->id}/enroll");

        $response->assertStatus(201);
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_student_cannot_enroll_twice()
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($student)
             ->postJson("/api/courses/{$course->id}/enroll");

        $response = $this->actingAs($student)
                         ->postJson("/api/courses/{$course->id}/enroll");

        $response->assertStatus(400);
        $response->assertJson(['message' => 'User already enrolled in this course.']);
    }
}
