import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CourseService } from '../../services/course.service';
import { AuthService } from '../../services/auth.service';
import { CourseCardComponent } from '../../shared/components/course-card/course-card.component';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-student-home',
  standalone: true,
  imports: [CommonModule, CourseCardComponent, RouterModule],
  templateUrl: './student-home.component.html',
  styleUrls: ['./student-home.component.scss']
})
export class StudentHomeComponent implements OnInit {
  recommendedCourses: any[] = [];
  popularCourses: any[] = [];
  inProgressCourses: any[] = [];
  freeCourses: any[] = [];
  userName: string = 'Student'; // Placeholder
  
  // Mock data for Sidebar
  learningPaths = [
    { title: 'Full Stack Web Developer', status: 'In Progress', coursesCompleted: 4, totalCourses: 12, id: 1 },
    { title: 'Data Analytics Associate', status: 'Next Up', coursesCompleted: 0, totalCourses: 8, id: 2 },
    { title: 'AI & Machine Learning', status: 'Future Goal', coursesCompleted: 0, totalCourses: 15, id: 3 }
  ];

  knowledgeHub = [
    { title: 'How to Build a Consistent Study Habit', readTime: '5 min read', date: 'June 12', image: 'assets/images/course-placeholder.svg' }, // using placeholder for now
    { title: 'Top 10 High-Income Skills to Learn in 2024', readTime: '8 min read', date: 'June 10', image: 'assets/images/course-placeholder.svg' },
    { title: 'Understanding AI: Why Everyone Should Code', readTime: '12 min read', date: 'June 05', image: 'assets/images/course-placeholder.svg' }
  ];

  constructor(
    private courseService: CourseService,
    private authService: AuthService
  ) {}

  ngOnInit() {
    this.loadData();
    const user = this.authService.getUser();
    if (user) {
      this.userName = user.name || 'Student';
    } else {
      this.userName = 'Guest';
    }
  }

  loadData() {
    // Recommended
    this.courseService.getRecommendedCourses().subscribe({
      next: (data: any) => this.recommendedCourses = data.data || data,
      error: (err) => console.error('Error loading recommended courses', err)
    });

    // Popular
    this.courseService.getPopularCourses().subscribe({
      next: (data: any) => this.popularCourses = data.data || data,
      error: (err) => console.error('Error loading popular courses', err)
    });

    // In Progress (My Courses)
    if (this.authService.isLoggedIn()) {
      this.courseService.getMyCourses().subscribe({
        next: (data: any) => {
          const courses = data.data || data;
          this.inProgressCourses = courses.slice(0, 2); 
        },
        error: (err) => console.error('Error loading my courses', err)
      });
    }

    // Free Courses
    this.courseService.getCourses({ price: 'free' }).subscribe({ 
      next: (data: any) => {
        const courses = data.data || data;
        // Filter locally just in case backend ignores 'free'
        this.freeCourses = courses.filter((c: any) => parseFloat(c.price) === 0).slice(0, 3);
      },
      error: (err) => console.error('Error loading free courses', err)
    });
  }
}
