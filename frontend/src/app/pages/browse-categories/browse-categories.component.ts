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
  selectedCategorySlug: string | null = null;
  searchQuery: string = '';
  loading = false;

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
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    });
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
}
