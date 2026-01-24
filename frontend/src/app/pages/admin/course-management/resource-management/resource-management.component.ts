import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../../core/services/admin.service';
import { ToastService } from '../../../../services/toast.service';

@Component({
  selector: 'app-resource-management',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './resource-management.component.html',
  styleUrl: './resource-management.component.scss'
})
export class ResourceManagementComponent implements OnInit {
  @Input() lessonId!: number;
  
  resources: any[] = [];
  isLoading = false;
  
  newResource = {
    title: '',
    url: '',
    type: 'file'
  };

  constructor(
    private adminService: AdminService,
    private toastService: ToastService
  ) {}

  ngOnInit() {
    if (this.lessonId) {
      this.loadResources();
    }
  }

  loadResources() {
    this.isLoading = true;
    this.adminService.getLessonResources(this.lessonId).subscribe({
      next: (data) => {
        this.resources = data;
        this.isLoading = false;
      },
      error: (err) => {
        console.error('Error loading resources', err);
        this.isLoading = false;
      }
    });
  }

  addResource() {
    if (!this.newResource.title || !this.newResource.url) {
      this.toastService.warning('Please fill in all fields');
      return;
    }

    this.adminService.createLessonResource(this.lessonId, this.newResource).subscribe({
      next: (res) => {
        this.resources.push(res);
        this.newResource = { title: '', url: '', type: 'file' };
        this.toastService.success('Resource added');
      },
      error: (err) => {
        console.error('Error adding resource', err);
        this.toastService.error('Failed to add resource');
      }
    });
  }

  deleteResource(id: number) {
    if (!confirm('Delete this resource?')) return;
    
    this.adminService.deleteLessonResource(id).subscribe({
      next: () => {
        this.resources = this.resources.filter(r => r.id !== id);
        this.toastService.success('Resource deleted');
      },
      error: (err) => {
        console.error('Error deleting resource', err);
        this.toastService.error('Failed to delete resource');
      }
    });
  }
}
