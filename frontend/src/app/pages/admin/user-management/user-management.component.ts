import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../core/services/admin.service';
import { ToastService } from '../../../services/toast.service';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

@Component({
  selector: 'app-user-management',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './user-management.component.html',
  styleUrl: './user-management.component.scss'
})
export class UserManagementComponent implements OnInit {
  users$: Observable<any> | null = null;
  searchTerm = '';
  selectedRole = '';
  selectedStatus = '';
  editingUser: any = null;
  showEditForm = false;
  showEnrollModal = false;
  enrollingUser: any = null;
  courses$: Observable<any> | null = null;
  selectedCourseId: number | null = null;

  // Pagination
  currentPage = 1;
  itemsPerPage = 10;
  totalItems = 0;

  // Mock Stats
  stats = [
    { label: 'Total Users', value: '12,840', change: '+12%', trend: 'up', icon: 'fas fa-users', bg: 'bg-blue' },
    { label: 'Active Students', value: '10,200', change: '+8%', trend: 'up', icon: 'fas fa-graduation-cap', bg: 'bg-green' },
    { label: 'Instructors', value: '2,440', change: '+3%', trend: 'up', icon: 'fas fa-chalkboard-teacher', bg: 'bg-purple' },
    { label: 'Pending Approvals', value: '156', change: '-5%', trend: 'down', icon: 'fas fa-hourglass-half', bg: 'bg-orange' }
  ];

  roles = ['student', 'instructor', 'admin'];

  constructor(
    private adminService: AdminService,
    private toastService: ToastService
  ) {}

  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers(): void {
    this.users$ = this.adminService.getAllUsers(this.searchTerm, this.selectedRole).pipe(
      map(response => {
        // Mock data augmentation for UI demonstration
        if (response.data) {
          let users = response.data.map((user: any) => ({
            ...user,
            // Mock status if not present
            status: user.status || (Math.random() > 0.2 ? 'Active' : (Math.random() > 0.5 ? 'Suspended' : 'Pending')),
            // Mock ID if needed (though usually present)
            displayId: user.id ? `#${88000 + user.id}` : '#88219',
            // Mock joined date if not present
            joinedDate: user.created_at ? new Date(user.created_at) : new Date(2023, 9, 12)
          }));

          // Frontend filtering for mock status
          if (this.selectedStatus) {
            users = users.filter((u: any) => u.status === this.selectedStatus);
          }

          response.data = users;
          // Update total items based on filtered result if we are filtering on frontend, 
          // or keep original total if we assume backend pagination (but here we filter visible data)
          this.totalItems = this.selectedStatus ? users.length : (response.total || users.length);
        }
        return response;
      })
    );
  }

  onSearch(): void {
    this.currentPage = 1;
    this.loadUsers();
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    // In a real app, pass page to service
    this.loadUsers(); 
  }

  getPages(total: number, perPage: number): number[] {
    const pages = Math.ceil(total / perPage);
    return Array(pages).fill(0).map((x, i) => i + 1);
  }

  openCreateUserModal(): void {
    this.editingUser = {
      name: '',
      email: '',
      password: '',
      role: 'student'
    };
    this.showEditForm = true;
  }

  editUser(user: any): void {
    this.editingUser = { ...user, password: '' }; // Initialize password as empty
    this.showEditForm = true;
  }

  saveUser(): void {
    if (this.editingUser) {
      const request$ = this.editingUser.id
        ? this.adminService.updateUser(this.editingUser.id, this.editingUser)
        : this.adminService.createUser(this.editingUser);

      request$.subscribe({
        next: () => {
          this.showEditForm = false;
          this.loadUsers();
          this.toastService.success(this.editingUser.id ? 'User updated successfully' : 'User created successfully');
        },
        error: (err) => {
          console.error('Error saving user:', err);
          this.toastService.error('Failed to save user');
        }
      });
    }
  }

  deleteUser(userId: number): void {
    if (confirm('Are you sure you want to delete this user?')) {
      this.adminService.deleteUser(userId).subscribe(() => {
        this.loadUsers();
        this.toastService.success('User deleted successfully');
      });
    }
  }

  openEnrollModal(user: any): void {
    this.enrollingUser = user;
    this.selectedCourseId = null;
    this.courses$ = this.adminService.getAllCoursesAdmin('', 'published');
    this.showEnrollModal = true;
  }

  enrollUser(): void {
    if (this.enrollingUser && this.selectedCourseId) {
      this.adminService.createEnrollment(this.enrollingUser.id, this.selectedCourseId).subscribe({
        next: () => {
          this.showEnrollModal = false;
          this.toastService.success('User enrolled successfully');
        },
        error: (err) => {
          console.error('Error enrolling user:', err);
          this.toastService.error('Failed to enroll user: ' + (err.error?.message || 'Unknown error'));
        }
      });
    }
  }

  cancelEnroll(): void {
    this.showEnrollModal = false;
    this.enrollingUser = null;
    this.selectedCourseId = null;
  }

  cancelEdit(): void {
    this.showEditForm = false;
    this.editingUser = null;
  }
}
