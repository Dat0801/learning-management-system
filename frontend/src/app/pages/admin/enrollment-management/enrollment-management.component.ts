import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminService } from '../../../core/services/admin.service';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-enrollment-management',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './enrollment-management.component.html',
  styleUrl: './enrollment-management.component.scss'
})
export class EnrollmentManagementComponent implements OnInit {
  enrollments$: Observable<any> | null = null;

  constructor(private adminService: AdminService) {}

  ngOnInit(): void {
    this.loadEnrollments();
  }

  loadEnrollments(): void {
    this.enrollments$ = this.adminService.getAllEnrollments();
  }

  deleteEnrollment(enrollmentId: number): void {
    if (confirm('Are you sure you want to delete this enrollment?')) {
      this.adminService.deleteEnrollment(enrollmentId).subscribe(() => {
        this.loadEnrollments();
        alert('Enrollment deleted successfully');
      });
    }
  }
}
