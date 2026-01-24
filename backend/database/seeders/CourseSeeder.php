<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Category;
use App\Models\User;
use App\Models\Lesson;
use Carbon\Carbon;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure instructor exists
        $instructor = User::firstOrCreate([
            'email' => 'instructor@example.com',
        ], [
            'name' => 'Instructor User',
            'password' => bcrypt('password'),
            'role' => 'instructor',
        ]);

        $courses = [
            [
                'title' => 'The Complete Web Developer Bootcamp',
                'description' => 'Become a full-stack web developer with just one course. HTML, CSS, Javascript, Node, React, MongoDB, Web3 and DApps.',
                'price' => 19.99,
                'category_slug' => 'development',
                'status' => 'published',
                'thumbnail' => 'https://img-c.udemycdn.com/course/240x135/1565838_e54e_18.jpg',
                'lessons' => [
                    ['title' => 'Introduction to HTML', 'content' => 'Learn the basics of HTML5', 'duration' => '10:00', 'is_preview' => true],
                    ['title' => 'CSS Styling', 'content' => 'Master CSS3 and Flexbox', 'duration' => '15:30', 'is_preview' => false],
                    ['title' => 'Javascript Basics', 'content' => 'Variables, loops, and functions', 'duration' => '20:00', 'is_preview' => false],
                    ['title' => 'DOM Manipulation', 'content' => 'Interact with the page', 'duration' => '12:45', 'is_preview' => false],
                    ['title' => 'React Introduction', 'content' => 'Components and State', 'duration' => '25:00', 'is_preview' => false],
                ]
            ],
            [
                'title' => 'Machine Learning A-Z™: Hands-On Python & R In Data Science',
                'description' => 'Learn to create Machine Learning Algorithms in Python and R from two Data Science experts. Code templates included.',
                'price' => 24.99,
                'category_slug' => 'development',
                'status' => 'published',
                'thumbnail' => 'https://img-c.udemycdn.com/course/240x135/950390_270f_3.jpg',
                'lessons' => [
                    ['title' => 'Data Preprocessing', 'content' => 'Cleaning and preparing data', 'duration' => '12:00', 'is_preview' => true],
                    ['title' => 'Linear Regression', 'content' => 'Simple and Multiple Linear Regression', 'duration' => '18:00', 'is_preview' => false],
                    ['title' => 'Logistic Regression', 'content' => 'Classification problems', 'duration' => '15:00', 'is_preview' => false],
                ]
            ],
            [
                'title' => 'The Complete Digital Marketing Course - 12 Courses in 1',
                'description' => 'Master Digital Marketing Strategy, Social Media Marketing, SEO, YouTube, Email, Facebook Marketing, Analytics & More!',
                'price' => 14.99,
                'category_slug' => 'marketing',
                'status' => 'published',
                'thumbnail' => 'https://img-c.udemycdn.com/course/240x135/914296_3670_8.jpg',
                'lessons' => [
                    ['title' => 'Market Research', 'content' => 'Define your audience', 'duration' => '08:00', 'is_preview' => true],
                    ['title' => 'WordPress Setup', 'content' => 'Building your website', 'duration' => '20:00', 'is_preview' => false],
                    ['title' => 'Email Marketing', 'content' => 'Building a mailing list', 'duration' => '15:00', 'is_preview' => false],
                ]
            ],
            [
                'title' => 'Graphic Design Masterclass - Learn GREAT Design',
                'description' => 'The Ultimate Graphic Design Course Which Covers Photoshop, Illustrator, InDesign, Design Theory, Branding and Logo Design.',
                'price' => 18.99,
                'category_slug' => 'design',
                'status' => 'published',
                'thumbnail' => 'https://img-c.udemycdn.com/course/240x135/1065428_f1e8_5.jpg',
                'lessons' => [
                    ['title' => 'Typography', 'content' => 'Fonts and hierarchy', 'duration' => '10:00', 'is_preview' => true],
                    ['title' => 'Color Theory', 'content' => 'Choosing the right colors', 'duration' => '12:00', 'is_preview' => false],
                    ['title' => 'Photoshop Basics', 'content' => 'Layers and masks', 'duration' => '25:00', 'is_preview' => false],
                ]
            ],
            [
                'title' => 'Microsoft Excel - Excel from Beginner to Advanced',
                'description' => 'Excel with this A-Z Microsoft Excel Course. Microsoft Excel 2010, 2013, 2016, Excel 2019 and Office 365.',
                'price' => 12.99,
                'category_slug' => 'office-productivity',
                'status' => 'published',
                'thumbnail' => 'https://img-c.udemycdn.com/course/240x135/793796_0e89_2.jpg',
                'lessons' => [
                    ['title' => 'Excel Interface', 'content' => 'Getting started', 'duration' => '05:00', 'is_preview' => true],
                    ['title' => 'Functions & Formulas', 'content' => 'SUM, AVERAGE, IF', 'duration' => '15:00', 'is_preview' => false],
                    ['title' => 'Pivot Tables', 'content' => 'Analyzing data', 'duration' => '20:00', 'is_preview' => false],
                ]
            ],
            [
                'title' => 'Pianoforall - Incredible New Way To Learn Piano & Keyboard',
                'description' => 'Imagine being able to sit down at a piano and just PLAY - Ballads, Pop, Blues, Jazz, Ragtime, Classical. Now you can!',
                'price' => 29.99,
                'category_slug' => 'music',
                'status' => 'published',
                'thumbnail' => 'https://img-c.udemycdn.com/course/240x135/238934_4d81_5.jpg',
                'lessons' => [
                    ['title' => 'Party Time - Rhythm Style', 'content' => 'Basic chords and rhythms', 'duration' => '10:00', 'is_preview' => true],
                    ['title' => 'Blues & Rock \'n\' Roll', 'content' => 'Left hand patterns', 'duration' => '15:00', 'is_preview' => false],
                ]
            ],
            [
                'title' => 'An Entire MBA in 1 Course: Award Winning Business School Prof',
                'description' => '** #1 Best Selling Business Course! ** Everything You Need to Know About Business from Start-up to IPO.',
                'price' => 99.99,
                'category_slug' => 'business',
                'status' => 'published',
                'thumbnail' => 'https://img-c.udemycdn.com/course/240x135/648826_f0e5_4.jpg',
                'lessons' => [
                    ['title' => 'Starting a Company', 'content' => 'Legal structures and equity', 'duration' => '20:00', 'is_preview' => true],
                    ['title' => 'Financial Analysis', 'content' => 'Reading a balance sheet', 'duration' => '30:00', 'is_preview' => false],
                ]
            ],
            [
                'title' => 'Photography Masterclass: A Complete Guide to Photography',
                'description' => 'The Best Online Professional Photography Class: How to Take Amazing Photos for Beginners & Advanced Photographers.',
                'price' => 13.99,
                'category_slug' => 'photography-video',
                'status' => 'published',
                'thumbnail' => 'https://img-c.udemycdn.com/course/240x135/129118_ade5_25.jpg',
                'lessons' => [
                    ['title' => 'Understanding Camera', 'content' => 'ISO, Aperture, Shutter Speed', 'duration' => '15:00', 'is_preview' => true],
                    ['title' => 'Composition', 'content' => 'Rule of thirds', 'duration' => '10:00', 'is_preview' => false],
                ]
            ],
        ];

        foreach ($courses as $courseData) {
            $category = Category::where('slug', $courseData['category_slug'])->first();
            
            if (!$category) {
                // Skip if category not found or create it? 
                // Better to skip or log, but for now we'll assume categories exist as per user requirement.
                continue;
            }

            $course = Course::updateOrCreate(
                [
                    'title' => $courseData['title'],
                    'instructor_id' => $instructor->id,
                ],
                [
                    'description' => $courseData['description'],
                    'price' => $courseData['price'],
                    'category_id' => $category->id,
                    'status' => $courseData['status'],
                    'published_at' => $courseData['status'] === 'published' ? Carbon::now() : null,
                    'thumbnail' => $courseData['thumbnail'],
                ]
            );

            // Create Lessons
            foreach ($courseData['lessons'] as $index => $lessonData) {
                Lesson::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'title' => $lessonData['title'],
                    ],
                    [
                        'content' => $lessonData['content'],
                        'duration' => $lessonData['duration'],
                        'is_preview' => $lessonData['is_preview'],
                        'order' => $index + 1,
                        // Add a placeholder video_url if you want
                        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 
                    ]
                );
            }
        }
    }
}
