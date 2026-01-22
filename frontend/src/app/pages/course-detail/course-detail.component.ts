import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterModule, Router } from '@angular/router';
import { CourseService } from '../../services/course.service';
import { AuthService } from '../../services/auth.service';
import { WishlistService } from '../../services/wishlist.service';
import { ToastService } from '../../services/toast.service';
import { Course, Lesson } from '../../models/course.model';
import { map, switchMap } from 'rxjs/operators';
import { of } from 'rxjs';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-course-detail',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './course-detail.component.html',
  styleUrls: ['./course-detail.component.scss']
})
export class CourseDetailComponent implements OnInit {
  course: Course | null = null;
  loading = true;
  currentLesson: Lesson | null = null;
  isInWishlist = false;
  
  // Reviews
  reviews: any[] = [];
  userHasReviewed = false;
  newReviewRating = 5;
  newReviewComment = '';
  submittingReview = false;
  currentUser: any = null;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private courseService: CourseService,
    public authService: AuthService,
    private wishlistService: WishlistService,
    private toastService: ToastService
  ) {}

  ngOnInit(): void {
    this.currentUser = this.authService.getUser();
    this.route.paramMap.pipe(
      switchMap(params => {
        const id = params.get('id');
        if (id) {
          return this.courseService.getCourse(+id);
        }
        return of(null);
      })
    ).subscribe({
      next: (response: any) => {
        this.course = response.data || response;
        this.determineLessonStatus();
        this.loading = false;
        if (this.course) {
          this.loadReviews();
          this.checkWishlistStatus();
        }
      },
      error: (err) => {
        console.error('Failed to load course', err);
        this.loading = false;
      }
    });
  }

  checkWishlistStatus() {
    if (!this.course) return;
    this.wishlistService.wishlist$.subscribe(ids => {
      if (this.course) {
        this.isInWishlist = ids.includes(this.course.id);
      }
    });
  }

  toggleWishlist() {
    if (!this.course) return;
    
    if (!this.authService.isLoggedIn()) {
      this.toastService.warning('Please login to use wishlist');
      return;
    }

    if (this.isInWishlist) {
      this.wishlistService.removeFromWishlist(this.course.id).subscribe();
    } else {
      this.wishlistService.addToWishlist(this.course.id).subscribe();
    }
  }

  loadReviews() {
    if (!this.course) return;
    this.courseService.getReviews(this.course.id).subscribe(reviews => {
      this.reviews = reviews;
      // Check if current user has reviewed
      const user = this.authService.getUser();
      if (user) {
        this.userHasReviewed = this.reviews.some(r => r.user_id === user.id);
      }
    });
  }

  submitReview() {
    if (!this.course) return;
    
    this.submittingReview = true;
    const reviewData = {
      rating: this.newReviewRating,
      comment: this.newReviewComment
    };

    this.courseService.createReview(this.course.id, reviewData).subscribe({
      next: (review) => {
        this.reviews.unshift(review); // Add to top
        this.userHasReviewed = true;
        this.submittingReview = false;
        this.newReviewComment = '';
        // Update course rating locally if needed, or just reload course
      },
      error: (err) => {
        console.error('Failed to submit review', err);
        this.toastService.error(err.error?.message || 'Failed to submit review');
        this.submittingReview = false;
      }
    });
  }

  deleteReview(reviewId: number) {
    if (!confirm('Are you sure you want to delete this review?')) return;

    this.courseService.deleteReview(reviewId).subscribe({
      next: () => {
        this.reviews = this.reviews.filter(r => r.id !== reviewId);
        this.userHasReviewed = false; // Allow user to review again
      },
      error: (err) => {
        console.error('Failed to delete review', err);
        this.toastService.error('Failed to delete review');
      }
    });
  }

  determineLessonStatus() {
    if (!this.course || !this.course.lessons) return;
// ... rest of file

    if (!this.course.is_enrolled) {
        // If not enrolled, maybe first lesson is free preview? 
        // For now, assuming only enrolled users see status, others see locked.
        return;
    }

    // Find the first non-completed lesson
    this.currentLesson = this.course.lessons.find(l => !l.is_completed) || null;
    
    // If all completed, maybe the last one is "current" or course is fully done
    if (!this.currentLesson && this.course.lessons.length > 0) {
        // Course completed
        this.currentLesson = this.course.lessons[this.course.lessons.length - 1];
    }
  }

  getLessonStatus(lesson: Lesson): 'completed' | 'current' | 'locked' | 'preview' {
    if (!this.course?.is_enrolled) {
      if (lesson.is_preview) {
        return 'preview';
      }
      return 'locked';
    }

    if (lesson.is_completed) {
      return 'completed';
    }

    if (this.currentLesson && lesson.id === this.currentLesson.id) {
      return 'current';
    }

    // If previous lesson is completed or this is the first lesson, it's unlocked (current/next)
    // But we are simplifying logic: 'current' is the one active.
    // If not active and not completed, check if it's accessible?
    // For now, let's assume sequential access or just locked if not current/completed.
    // Actually, users can usually jump back to completed lessons.
    // Jump forward? Depends on policy.
    
    // For now, if enrolled, allow access to all? Or just sequential?
    // Let's return 'locked' only if strict sequential.
    // But typical LMS allows jumping to any lesson if enrolled (or at least previous ones).
    
    return 'locked'; 
  }

  enroll() {
    if (!this.course) return;
    
    if (!this.authService.isLoggedIn()) {
      this.toastService.warning('Please login to enroll');
      this.router.navigate(['/auth/login'], { queryParams: { returnUrl: this.router.url } });
      return;
    }

    this.courseService.enroll(this.course.id).subscribe({
      next: () => {
        this.toastService.success('Enrolled successfully!');
        // Refresh course data
        if (this.course) {
            this.course.is_enrolled = true;
            this.determineLessonStatus();
        }
      },
      error: (err) => {
        this.toastService.error('Failed to enroll: ' + (err.error?.message || err.message));
      }
    });
  }
}
