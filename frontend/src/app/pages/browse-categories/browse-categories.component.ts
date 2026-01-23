import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { CourseService } from '../../services/course.service';
import { CourseCardComponent } from '../../shared/components/course-card/course-card.component';
import { FormsModule } from '@angular/forms';
import { map } from 'rxjs/operators';

@Component({
  selector: 'app-browse-categories',
  standalone: true,
  imports: [CommonModule, RouterModule, CourseCardComponent, FormsModule],
  templateUrl: './browse-categories.component.html',
  styleUrl: './browse-categories.component.scss'
})
export class BrowseCategoriesComponent implements OnInit {
  categories: any[] = [];
  courses: any[] = [];
  filteredCourses: any[] = []; // For client-side filtering if needed
  selectedCategorySlug: string | null = null;
  searchQuery: string = '';
  loading = false;
  
  // Filters
  priceFilter: 'all' | 'free' | 'paid' = 'all';
  levelFilter: {[key: string]: boolean} = {
    'Beginner': false,
    'Intermediate': false,
    'Expert': false
  };
  ratingFilter: number | null = null;
  
  // Sorting & Tabs
  sortBy: string = 'most-popular';
  activeTab: 'all' | 'top-rated' | 'newest' | 'best-sellers' = 'all';

  constructor(
    private courseService: CourseService,
    private route: ActivatedRoute,
    private router: Router
  ) {}

  ngOnInit() {
    this.loadCategories();
    
    this.route.queryParams.subscribe(params => {
      this.selectedCategorySlug = params['category'] || null;
      this.searchQuery = params['search'] || '';
      
      // Parse price filter from query params
      const priceParam = params['price'];
      if (priceParam === 'free' || priceParam === 'paid' || priceParam === 'all') {
        this.priceFilter = priceParam;
      }
      
      this.loadCourses();
    });
  }

  loadCategories() {
    this.courseService.getCategories().subscribe(res => {
      this.categories = res;
    });
  }

  loadCourses() {
    this.loading = true;
    const filters: any = {};
    if (this.selectedCategorySlug) {
      filters.category_slug = this.selectedCategorySlug;
    }
    if (this.searchQuery) {
      filters.search = this.searchQuery;
    }

    this.courseService.getCourses(filters).pipe(
      map((res: any) => res.data || res) // Handle wrapped or unwrapped data
    ).subscribe({
      next: (courses) => {
        this.courses = courses;
        this.applyClientFilters(); // Apply client-side filters
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    });
  }

  applyClientFilters() {
    let result = [...this.courses];
    
    // Price Filter
    if (this.priceFilter === 'free') {
      result = result.filter(c => c.price == 0);
    } else if (this.priceFilter === 'paid') {
      result = result.filter(c => c.price > 0);
    }

    // Rating Filter
    if (this.ratingFilter) {
      result = result.filter(c => (c.average_rating || 0) >= this.ratingFilter!);
    }
    
    // Level Filter (Mock implementation since backend doesn't have level yet)
    // If we had levels, we would check if any selected level matches
    // const selectedLevels = Object.keys(this.levelFilter).filter(k => this.levelFilter[k]);
    // if (selectedLevels.length > 0) {
    //   result = result.filter(c => selectedLevels.includes(c.level));
    // }

    // Sorting/Tabs
    if (this.activeTab === 'top-rated') {
      result.sort((a, b) => (b.average_rating || 0) - (a.average_rating || 0));
    } else if (this.activeTab === 'newest') {
      result.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
    } else if (this.activeTab === 'best-sellers' || this.sortBy === 'most-popular') {
       // Mock best seller by enrollment count if available, or just random
       result.sort((a, b) => (b.reviews_count || 0) - (a.reviews_count || 0));
    }

    this.filteredCourses = result;
  }

  onCategorySelect(slug: string | null) {
    this.router.navigate([], {
      relativeTo: this.route,
      queryParams: { category: slug, search: this.searchQuery },
      queryParamsHandling: 'merge',
    });
  }

  onSearch() {
    this.router.navigate([], {
      relativeTo: this.route,
      queryParams: { search: this.searchQuery },
      queryParamsHandling: 'merge',
    });
  }
  
  updatePriceFilter(value: 'all' | 'free' | 'paid') {
    this.priceFilter = value;
    this.applyClientFilters();
  }
  
  updateRatingFilter(rating: number | null) {
    if (this.ratingFilter === rating) {
      this.ratingFilter = null; // Toggle off
    } else {
      this.ratingFilter = rating;
    }
    this.applyClientFilters();
  }
  
  setActiveTab(tab: 'all' | 'top-rated' | 'newest' | 'best-sellers') {
    this.activeTab = tab;
    this.applyClientFilters();
  }
}
