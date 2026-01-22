import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../core/services/admin.service';
import { ToastService } from '../../../services/toast.service';
import { Observable } from 'rxjs';

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
  editingUser: any = null;
  showEditForm = false;
  showEnrollModal = false;
  enrollingUser: any = null;
  courses$: Observable<any> | null = null;
  selectedCourseId: number | null = null;

  roles = ['student', 'instructor', 'admin'];

  constructor(
    private adminService: AdminService,
    private toastService: ToastService
  ) {}

  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers(): void {
    this.users$ = this.adminService.getAllUsers(this.searchTerm, this.selectedRole);
  }

  onSearch(): void {
    this.loadUsers();
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
