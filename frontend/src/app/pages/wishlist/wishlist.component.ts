import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { WishlistService } from '../../services/wishlist.service';
import { CourseCardComponent } from '../../shared/components/course-card/course-card.component';

@Component({
  selector: 'app-wishlist',
  standalone: true,
  imports: [CommonModule, CourseCardComponent],
  template: `
    <div class="page-container">
      <h1 class="page-title">My Wishlist</h1>
      
      <div *ngIf="loading" class="loading">
        Loading...
      </div>

      <div *ngIf="!loading && courses.length === 0" class="empty-state">
        <p>Your wishlist is empty.</p>
        <a href="/browse" class="btn-primary">Browse Courses</a>
      </div>

      <div *ngIf="!loading && courses.length > 0" class="course-grid">
        <app-course-card
          *ngFor="let course of courses"
          [course]="course"
        ></app-course-card>
      </div>
    </div>
  `,
  styles: [`
    .page-title {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 2rem;
      color: var(--text-main);
    }

    .course-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 2rem;
    }

    .empty-state {
      text-align: center;
      padding: 4rem 0;
      
      p {
        color: var(--text-muted);
        margin-bottom: 1.5rem;
      }
    }
  `]
})
export class WishlistComponent implements OnInit {
  courses: any[] = [];
  loading = true;

  constructor(private wishlistService: WishlistService) {}

  ngOnInit() {
    this.loadWishlist();
  }

  loadWishlist() {
    this.wishlistService.getWishlist().subscribe({
      next: (courses) => {
        this.courses = courses;
        this.loading = false;
      },
      error: (err) => {
        console.error('Failed to load wishlist', err);
        this.loading = false;
      }
    });
  }
}
