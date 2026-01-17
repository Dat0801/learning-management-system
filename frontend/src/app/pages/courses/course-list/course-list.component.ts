import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
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
  filteredCourses: any[] = [];
  errorMessage: string = '';
  successMessage: string = '';
  searchTerm = '';

  constructor(
    private courseService: CourseService,
    private authService: AuthService,
    private router: Router,
    private route: ActivatedRoute
  ) { }

  ngOnInit(): void {
    this.route.queryParamMap.subscribe(params => {
      this.searchTerm = params.get('q') || '';
      this.applyFilter();
    });
    this.loadCourses();
  }

  loadCourses() {
    this.courseService.getCourses().subscribe({
      next: (data) => {
        if (Array.isArray(data)) {
          this.courses = data;
        } else if (data && Array.isArray((data as any).data)) {
          this.courses = (data as any).data;
        } else {
          this.courses = [];
        }
        this.applyFilter();
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

  private applyFilter() {
    const term = this.searchTerm.trim().toLowerCase();

    if (!term) {
      this.filteredCourses = [...this.courses];
      return;
    }

    this.filteredCourses = this.courses.filter(course => {
      const title = (course.title || '').toLowerCase();
      const description = (course.description || '').toLowerCase();
      return title.includes(term) || description.includes(term);
    });
  }
}
