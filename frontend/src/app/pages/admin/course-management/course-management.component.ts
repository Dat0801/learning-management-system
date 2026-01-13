import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../core/services/admin.service';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-course-management',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './course-management.component.html',
  styleUrl: './course-management.component.scss'
})
export class CourseManagementComponent implements OnInit {
  courses$: Observable<any> | null = null;
  searchTerm = '';
  selectedStatus = '';
  editingCourse: any = null;
  showEditForm = false;

  statuses = ['draft', 'published', 'archived'];

  constructor(private adminService: AdminService) {}

  ngOnInit(): void {
    this.loadCourses();
  }

  loadCourses(): void {
    this.courses$ = this.adminService.getAllCoursesAdmin(this.searchTerm, this.selectedStatus);
  }

  onSearch(): void {
    this.loadCourses();
  }

  editCourse(course: any): void {
    this.editingCourse = { ...course };
    this.showEditForm = true;
  }

  saveCourse(): void {
    if (this.editingCourse) {
      this.adminService.updateCourseAdmin(this.editingCourse.id, this.editingCourse)
        .subscribe(() => {
          this.showEditForm = false;
          this.loadCourses();
          alert('Course updated successfully');
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
