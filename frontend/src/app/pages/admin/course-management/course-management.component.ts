import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../core/services/admin.service';
import { ToastService } from '../../../services/toast.service';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { CurriculumManagementComponent } from './curriculum-management/curriculum-management.component';

@Component({
  selector: 'app-course-management',
  standalone: true,
  imports: [CommonModule, FormsModule, CurriculumManagementComponent],
  templateUrl: './course-management.component.html',
  styleUrl: './course-management.component.scss'
})
export class CourseManagementComponent implements OnInit {
  protected Math = Math;
  courses$: Observable<any> | null = null;
  categories: any[] = [];
  searchTerm = '';
  selectedStatus = '';
  selectedCategory = '';
  editingCourse: any = null;
  showEditForm = false;

  // Pagination
  currentPage = 1;
  itemsPerPage = 5;
  totalItems = 0;
  totalPages = 0;
  paginatedCourses: any[] = [];

  // Summary Stats
  stats = [
    { label: 'Total Courses', value: '0', change: '0', trend: 'neutral', icon: 'fas fa-book', bg: 'bg-blue' },
    { label: 'Published', value: '0', change: '0', trend: 'neutral', icon: 'fas fa-check-circle', bg: 'bg-green' },
    { label: 'Draft', value: '0', change: '0', trend: 'neutral', icon: 'fas fa-pencil-alt', bg: 'bg-orange' },
    { label: 'Archived', value: '0', change: '0', trend: 'neutral', icon: 'fas fa-archive', bg: 'bg-purple' }
  ];

  statuses = ['draft', 'published', 'archived'];

  constructor(
    private adminService: AdminService,
    private toastService: ToastService
  ) {}

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
    this.courses$ = this.adminService.getAllCoursesAdmin(this.searchTerm, this.selectedStatus).pipe(
      map(response => {
        let courses = response.data || [];
        
        // Filter by category if selected
        if (this.selectedCategory) {
          courses = courses.filter((c: any) => c.category_id == this.selectedCategory);
        }

        // Calculate Stats
        const total = courses.length;
        const published = courses.filter((c: any) => c.status === 'published').length;
        const draft = courses.filter((c: any) => c.status === 'draft').length;
        const archived = courses.filter((c: any) => c.status === 'archived').length;

        this.stats = [
          { label: 'Total Courses', value: total.toString(), change: '+0%', trend: 'neutral', icon: 'fas fa-book', bg: 'bg-blue' },
          { label: 'Published', value: published.toString(), change: '+0%', trend: 'up', icon: 'fas fa-check-circle', bg: 'bg-green' },
          { label: 'Draft', value: draft.toString(), change: '+0%', trend: 'neutral', icon: 'fas fa-pencil-alt', bg: 'bg-orange' },
          { label: 'Archived', value: archived.toString(), change: '+0%', trend: 'neutral', icon: 'fas fa-archive', bg: 'bg-purple' }
        ];

        // Pagination Logic (Frontend Pagination for now)
        this.totalItems = courses.length;
        this.totalPages = Math.ceil(this.totalItems / this.itemsPerPage);
        this.updatePaginatedCourses(courses);

        return { ...response, data: courses };
      })
    );
  }

  updatePaginatedCourses(courses: any[]): void {
    const startIndex = (this.currentPage - 1) * this.itemsPerPage;
    const endIndex = startIndex + this.itemsPerPage;
    this.paginatedCourses = courses.slice(startIndex, endIndex);
  }

  onSearch(): void {
    this.currentPage = 1; // Reset to first page on search
    this.loadCourses();
  }

  onPageChange(page: number): void {
    if (page >= 1 && page <= this.totalPages) {
      this.currentPage = page;
      // Re-trigger load to apply pagination (or just slice if we cache the full list)
      // Since we are using observable pipe, we might need to store the full list to paginate efficiently without re-fetching
      // But for now, let's just reload which triggers the pipe logic again
      this.loadCourses(); 
    }
  }

  getPages(totalItems: number, itemsPerPage: number): number[] {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const pages = [];
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i);
    }
    // Limit visible pages if too many (optional, but good for UI)
    // For simplicity, returning all for now or a slice
    return pages;
  }
  
  clearFilters(): void {
    this.searchTerm = '';
    this.selectedStatus = '';
    this.selectedCategory = '';
    this.onSearch();
  }

  openCreateCourseModal(): void {
    this.editingCourse = {
      title: '',
      description: '',
      price: 0,
      status: 'draft',
      category_id: null,
      instructor_id: null, // Will be handled by backend if current user, or need UI to select
      lessons: []
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

  onLessonsChange(lessons: any[]): void {
    if (this.editingCourse) {
      this.editingCourse.lessons = lessons;
    }
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
          this.toastService.success(this.editingCourse.id ? 'Course updated successfully' : 'Course created successfully');
        },
        error: (err) => {
          console.error('Error saving course:', err);
          this.toastService.error('Failed to save course: ' + (err.error?.message || 'Unknown error'));
        }
      });
    }
  }

  deleteCourse(courseId: number): void {
    if (confirm('Are you sure you want to delete this course?')) {
      this.adminService.deleteCourseAdmin(courseId).subscribe(() => {
        this.loadCourses();
        this.toastService.success('Course deleted successfully');
      });
    }
  }

  cancelEdit(): void {
    this.showEditForm = false;
    this.editingCourse = null;
  }
}
