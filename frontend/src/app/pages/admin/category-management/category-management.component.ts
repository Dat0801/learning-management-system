import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../core/services/admin.service';
import { ToastService } from '../../../services/toast.service';

@Component({
  selector: 'app-category-management',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './category-management.component.html',
  styleUrl: './category-management.component.scss'
})
export class CategoryManagementComponent implements OnInit {
  categories: any[] = [];
  filteredCategories: any[] = [];
  displayedCategories: any[] = [];
  searchTerm: string = '';
  isLoading = false;
  showModal = false;
  editingCategory: any = {
    name: '',
    slug: '',
    description: '',
    parent_id: null
  };

  // Pagination
  currentPage = 1;
  itemsPerPage = 10;
  totalItems = 0;

  // Stats
  stats = [
    { label: 'Total Categories', value: '0', change: '+0%', trend: 'up', icon: 'fas fa-layer-group', bg: 'bg-blue' },
    { label: 'Total Courses', value: '0', change: '+0%', trend: 'up', icon: 'fas fa-graduation-cap', bg: 'bg-green' },
    { label: 'Top Category', value: '-', change: 'Most Popular', trend: 'up', icon: 'fas fa-star', bg: 'bg-purple' },
    { label: 'Active', value: '0', change: '100%', trend: 'up', icon: 'fas fa-check-circle', bg: 'bg-orange' }
  ];

  constructor(
    private adminService: AdminService,
    private toastService: ToastService
  ) {}

  ngOnInit() {
    this.loadCategories();
  }

  loadCategories() {
    this.isLoading = true;
    this.adminService.getAllCategories().subscribe({
      next: (data) => {
        this.categories = data;
        this.calculateStats();
        this.filterCategories();
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading categories:', error);
        this.toastService.error('Failed to load categories');
        this.isLoading = false;
      }
    });
  }

  calculateStats() {
    const totalCategories = this.categories.length;
    const totalCourses = this.categories.reduce((acc, cat) => acc + (cat.courses_count || 0), 0);
    
    // Find top category
    let maxCourses = -1;
    let topCategory = '-';
    this.categories.forEach(cat => {
      if ((cat.courses_count || 0) > maxCourses) {
        maxCourses = cat.courses_count || 0;
        topCategory = cat.name;
      }
    });

    this.stats = [
      { label: 'Total Categories', value: totalCategories.toString(), change: '+12%', trend: 'up', icon: 'fas fa-layer-group', bg: 'bg-blue' },
      { label: 'Total Courses', value: totalCourses.toString(), change: '+8%', trend: 'up', icon: 'fas fa-graduation-cap', bg: 'bg-green' },
      { label: 'Top Category', value: topCategory, change: `${maxCourses} Courses`, trend: 'up', icon: 'fas fa-star', bg: 'bg-purple' },
      { label: 'Active', value: totalCategories.toString(), change: '100%', trend: 'neutral', icon: 'fas fa-check-circle', bg: 'bg-orange' }
    ];
  }

  filterCategories() {
    let filtered = this.categories;

    if (this.searchTerm) {
      const term = this.searchTerm.toLowerCase();
      filtered = filtered.filter(cat => 
        cat.name.toLowerCase().includes(term) || 
        cat.slug.toLowerCase().includes(term) ||
        (cat.description && cat.description.toLowerCase().includes(term))
      );
    }

    this.totalItems = filtered.length;
    this.filteredCategories = filtered;
    this.updateDisplayedCategories();
  }

  updateDisplayedCategories() {
    const startIndex = (this.currentPage - 1) * this.itemsPerPage;
    const endIndex = startIndex + this.itemsPerPage;
    this.displayedCategories = this.filteredCategories.slice(startIndex, endIndex);
  }

  onSearch() {
    this.currentPage = 1;
    this.filterCategories();
  }

  onPageChange(page: number) {
    this.currentPage = page;
    this.updateDisplayedCategories();
  }

  getPages(total: number, perPage: number): number[] {
    const pages = Math.ceil(total / perPage);
    return Array(pages).fill(0).map((x, i) => i + 1);
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
    if (!this.editingCategory.name || !this.editingCategory.slug) {
      this.toastService.warning('Name and Slug are required');
      return;
    }

    const request$ = this.editingCategory.id
      ? this.adminService.updateCategory(this.editingCategory.id, this.editingCategory)
      : this.adminService.createCategory(this.editingCategory);

    request$.subscribe({
      next: () => {
        this.toastService.success(
          this.editingCategory.id ? 'Category updated successfully' : 'Category created successfully'
        );
        this.closeModal();
        this.loadCategories();
      },
      error: (error) => {
        console.error('Error saving category:', error);
        this.toastService.error('Failed to save category');
      }
    });
  }

  deleteCategory(id: number) {
    if (confirm('Are you sure you want to delete this category?')) {
      this.adminService.deleteCategory(id).subscribe({
        next: () => {
          this.toastService.success('Category deleted successfully');
          this.loadCategories();
        },
        error: (error) => {
          console.error('Error deleting category:', error);
          this.toastService.error('Failed to delete category');
        }
      });
    }
  }
}
