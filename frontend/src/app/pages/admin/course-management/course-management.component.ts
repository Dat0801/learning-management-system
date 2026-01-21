import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../core/services/admin.service';
import { Observable } from 'rxjs';
import { CurriculumManagementComponent } from './curriculum-management/curriculum-management.component';

@Component({
  selector: 'app-course-management',
  standalone: true,
  imports: [CommonModule, FormsModule, CurriculumManagementComponent],
  templateUrl: './course-management.component.html',
  styleUrl: './course-management.component.scss'
})
export class CourseManagementComponent implements OnInit {
  courses$: Observable<any> | null = null;
  categories: any[] = [];
  searchTerm = '';
  selectedStatus = '';
  editingCourse: any = null;
  showEditForm = false;

  statuses = ['draft', 'published', 'archived'];

  constructor(private adminService: AdminService) {}

  ngOnInit(): void {
    this.loadCourses();
    this.loadCategories();
  }

  loadCategories(): void {
    this.adminService.getAllCategories().subscribe(data => {
      this.categories = data;
    });
  }

  loadCourses(): void {
    this.courses$ = this.adminService.getAllCoursesAdmin(this.searchTerm, this.selectedStatus);
  }

  onSearch(): void {
    this.loadCourses();
  }

  openCreateCourseModal(): void {
    this.editingCourse = {
      title: '',
      description: '',
      price: 0,
      status: 'draft',
      category_id: null,
      instructor_id: null // Will be handled by backend if current user, or need UI to select
    };
    // For now, assume current admin is the instructor or backend assigns it.
    // However, backend createCourseAdmin requires instructor_id.
    // We might need to fetch instructors or assign current user.
    // Let's assume we assign the current logged-in user for now if they are admin/instructor.
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    this.editingCourse.instructor_id = user.id;

    this.showEditForm = true;
  }

  editCourse(course: any): void {
    this.editingCourse = { ...course };
    this.showEditForm = true;
  }

  saveCourse(): void {
    if (this.editingCourse) {
      const request$ = this.editingCourse.id
        ? this.adminService.updateCourseAdmin(this.editingCourse.id, this.editingCourse)
        : this.adminService.createCourseAdmin(this.editingCourse);

      request$.subscribe({
        next: () => {
          this.showEditForm = false;
          this.loadCourses();
          alert(this.editingCourse.id ? 'Course updated successfully' : 'Course created successfully');
        },
        error: (err) => {
          console.error('Error saving course:', err);
          alert('Failed to save course: ' + (err.error?.message || 'Unknown error'));
        }
      });
    }
  }

  deleteCourse(courseId: number): void {
    if (confirm('Are you sure you want to delete this course?')) {
      this.adminService.deleteCourseAdmin(courseId).subscribe(() => {
        this.loadCourses();
        alert('Course deleted successfully');
      });
    }
  }

  cancelEdit(): void {
    this.showEditForm = false;
    this.editingCourse = null;
  }
}
