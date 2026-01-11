import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { CourseService } from '../../../services/course.service';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-course-list',
  templateUrl: './course-list.component.html',
  styleUrls: ['./course-list.component.scss'],
  standalone: false
})
export class CourseListComponent implements OnInit {
  courses: any[] = [];
  errorMessage: string = '';
  successMessage: string = '';

  constructor(
    private courseService: CourseService,
    private authService: AuthService,
    private router: Router
  ) { }

  ngOnInit(): void {
    this.loadCourses();
  }

  loadCourses() {
    this.courseService.getCourses().subscribe({
      next: (data) => {
        this.courses = data;
      },
      error: (error) => {
        this.errorMessage = 'Failed to load courses.';
        console.error(error);
      }
    });
  }

  enroll(courseId: number) {
    this.successMessage = '';
    this.errorMessage = '';

    if (!this.authService.isLoggedIn()) {
      this.router.navigate(['/auth/login']);
      return;
    }

    if (!confirm('Are you sure you want to enroll in this course?')) {
      return;
    }

    this.courseService.enroll(courseId).subscribe({
      next: (response) => {
        this.successMessage = 'Successfully enrolled in the course!';
        // Optionally refresh course list or update local state if needed
      },
      error: (error) => {
        this.errorMessage = error.error?.message || 'Failed to enroll in the course.';
      }
    });
  }
}
