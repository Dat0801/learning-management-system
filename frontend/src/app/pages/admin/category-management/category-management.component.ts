import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../core/services/admin.service';

@Component({
  selector: 'app-category-management',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './category-management.component.html',
  styleUrl: './category-management.component.scss'
})
export class CategoryManagementComponent implements OnInit {
  categories: any[] = [];
  isLoading = false;
  showModal = false;
  editingCategory: any = {
    name: '',
    slug: '',
    description: '',
    parent_id: null
  };

  constructor(private adminService: AdminService) {}

  ngOnInit() {
    this.loadCategories();
  }

  loadCategories() {
    this.isLoading = true;
    this.adminService.getAllCategories().subscribe({
      next: (data) => {
        this.categories = data;
        this.isLoading = false;
      },
      error: (err) => {
        console.error('Error loading categories', err);
        this.isLoading = false;
      }
    });
  }

  openCreateModal() {
    this.editingCategory = {
      name: '',
      slug: '',
      description: '',
      parent_id: null
    };
    this.showModal = true;
  }

  editCategory(category: any) {
    this.editingCategory = { ...category };
    this.showModal = true;
  }

  closeModal() {
    this.showModal = false;
    this.editingCategory = null;
  }

  generateSlug() {
    if (this.editingCategory.name) {
      this.editingCategory.slug = this.editingCategory.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)+/g, '');
    }
  }

  saveCategory() {
    const request$ = this.editingCategory.id
      ? this.adminService.updateCategory(this.editingCategory.id, this.editingCategory)
      : this.adminService.createCategory(this.editingCategory);

    request$.subscribe({
      next: () => {
        this.loadCategories();
        this.closeModal();
      },
      error: (err) => {
        console.error('Error saving category', err);
        alert('Failed to save category');
      }
    });
  }

  deleteCategory(id: number) {
    if (confirm('Are you sure you want to delete this category?')) {
      this.adminService.deleteCategory(id).subscribe({
        next: () => {
          this.loadCategories();
        },
        error: (err) => {
          console.error('Error deleting category', err);
          alert('Failed to delete category (Ensure it has no courses)');
        }
      });
    }
  }
}
