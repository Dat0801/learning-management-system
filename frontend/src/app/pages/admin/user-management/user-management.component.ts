import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../core/services/admin.service';
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

  roles = ['student', 'instructor', 'admin'];

  constructor(private adminService: AdminService) {}

  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers(): void {
    this.users$ = this.adminService.getAllUsers(this.searchTerm, this.selectedRole);
  }

  onSearch(): void {
    this.loadUsers();
  }

  editUser(user: any): void {
    this.editingUser = { ...user };
    this.showEditForm = true;
  }

  saveUser(): void {
    if (this.editingUser) {
      this.adminService.updateUser(this.editingUser.id, this.editingUser)
        .subscribe(() => {
          this.showEditForm = false;
          this.loadUsers();
          alert('User updated successfully');
        });
    }
  }

  deleteUser(userId: number): void {
    if (confirm('Are you sure you want to delete this user?')) {
      this.adminService.deleteUser(userId).subscribe(() => {
        this.loadUsers();
        alert('User deleted successfully');
      });
    }
  }

  cancelEdit(): void {
    this.showEditForm = false;
    this.editingUser = null;
  }
}
