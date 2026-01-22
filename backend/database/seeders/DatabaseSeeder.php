<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Categories
        $categories = [
            ['name' => 'Development', 'slug' => 'development'],
            ['name' => 'Business', 'slug' => 'business'],
            ['name' => 'Finance & Accounting', 'slug' => 'finance-accounting'],
            ['name' => 'IT & Software', 'slug' => 'it-software'],
            ['name' => 'Office Productivity', 'slug' => 'office-productivity'],
            ['name' => 'Personal Development', 'slug' => 'personal-development'],
            ['name' => 'Design', 'slug' => 'design'],
            ['name' => 'Marketing', 'slug' => 'marketing'],
            ['name' => 'Lifestyle', 'slug' => 'lifestyle'],
            ['name' => 'Photography & Video', 'slug' => 'photography-video'],
            ['name' => 'Health & Fitness', 'slug' => 'health-fitness'],
            ['name' => 'Music', 'slug' => 'music'],
            ['name' => 'Teaching & Academics', 'slug' => 'teaching-academics'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']]
            );
        }

        // 2. Create Users
        User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $instructor = User::firstOrCreate([
            'email' => 'instructor@example.com',
        ], [
            'name' => 'Instructor User',
            'password' => bcrypt('password'),
            'role' => 'instructor',
        ]);

        User::firstOrCreate([
            'email' => 'student@example.com',
        ], [
            'name' => 'Student User',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // 3. Create Courses (Only if instructor has none)
        // We only seed courses if the instructor doesn't have any, to avoid duplicating 
        // courses every time the seeder is run.
        if ($instructor->courses()->count() === 0) {
            $cats = Category::all();

            Course::factory(5)->create([
                'instructor_id' => $instructor->id,
            ])->each(function ($course) use ($cats) {
                // Assign a random category
                if ($cats->count() > 0) {
                    $course->update(['category_id' => $cats->random()->id]);
                }

                // Create 5 lessons for each course
                // Ensure at least one is a preview
                Lesson::factory()->create([
                    'course_id' => $course->id,
                    'is_preview' => true,
                    'order' => 1,
                    'title' => 'Introduction (Preview)',
                ]);

                Lesson::factory(4)->create([
                    'course_id' => $course->id,
                ]);
            });
        }
    }
}
