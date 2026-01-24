import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../core/services/admin.service';
import { ToastService } from '../../../services/toast.service';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

@Component({
  selector: 'app-enrollment-management',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './enrollment-management.component.html',
  styleUrl: './enrollment-management.component.scss'
})
export class EnrollmentManagementComponent implements OnInit {
  enrollments$: Observable<any> | null = null;
  
  // Filters
  searchTerm = '';
  selectedStatus = '';
  selectedDateRange = '30'; // Last 30 days
  
  // Pagination
  currentPage = 1;
  itemsPerPage = 10;
  totalItems = 0;

  // Stats
  stats = [
    { label: 'Total Revenue (This Month)', value: '$12,450.00', change: '+12.5%', trend: 'up', icon: 'fas fa-dollar-sign', bg: 'bg-blue' },
    { label: 'Active Subscriptions', value: '1,284', change: '+5.2%', trend: 'up', icon: 'fas fa-user-friends', bg: 'bg-green' },
    { label: 'Avg. Order Value', value: '$142.00', change: 'Stable', trend: 'neutral', icon: 'fas fa-shopping-cart', bg: 'bg-purple' },
    { label: 'Refund Rate', value: '0.8%', change: '-0.3%', trend: 'down', icon: 'fas fa-undo', bg: 'bg-orange' } // down is good for refund rate, but we'll use trend color logic
  ];

  constructor(
    private adminService: AdminService,
    private toastService: ToastService
  ) {}

  ngOnInit(): void {
    this.loadEnrollments();
  }

  loadEnrollments(): void {
    this.enrollments$ = this.adminService.getAllEnrollments(this.selectedStatus).pipe(
      map(response => {
        // Mocking additional data for UI matching
        const data = response.data.map((enrollment: any) => ({
          ...enrollment,
          student_email: enrollment.user?.email || 'student@example.com',
          course_category: enrollment.course?.category?.name || 'Development',
          payment_method: this.getRandomPaymentMethod(),
          amount: enrollment.course?.price || 0,
          payment_status: enrollment.status === 'active' ? 'Paid' : (enrollment.status === 'pending' ? 'Pending' : 'Refunded')
        }));
        
        this.totalItems = response.total || data.length;
        return { ...response, data };
      })
    );
  }

  getRandomPaymentMethod() {
    const methods = [
      { name: 'Stripe (Visa)', icon: 'fab fa-stripe' },
      { name: 'PayPal', icon: 'fab fa-paypal' },
      { name: 'Bank Transfer', icon: 'fas fa-university' },
      { name: 'Stripe (MasterCard)', icon: 'fab fa-stripe' }
    ];
    return methods[Math.floor(Math.random() * methods.length)];
  }

  deleteEnrollment(enrollmentId: number): void {
    if (confirm('Are you sure you want to delete this enrollment?')) {
      this.adminService.deleteEnrollment(enrollmentId).subscribe(() => {
        this.loadEnrollments();
        this.toastService.success('Enrollment deleted successfully');
      });
    }
  }
  
  onPageChange(page: number): void {
    this.currentPage = page;
    // Implement server-side pagination if available, or client-side slicing
    // For now, we'll reload (assuming server might handle page param later)
    this.loadEnrollments(); 
  }

  getPages(total: number, perPage: number): number[] {
    const pages = Math.ceil(total / perPage);
    return Array.from({ length: pages }, (_, i) => i + 1);
  }

  getInitials(name: string): string {
    return name ? name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'NA';
  }
  
  getAvatarColor(name: string): string {
    const colors = ['bg-blue-100 text-blue-600', 'bg-green-100 text-green-600', 'bg-purple-100 text-purple-600', 'bg-pink-100 text-pink-600'];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
      hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
  }
}
