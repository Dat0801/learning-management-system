import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { WishlistService } from '../../../services/wishlist.service';
import { AuthService } from '../../../services/auth.service';
import { ToastService } from '../../../services/toast.service';

@Component({
  selector: 'app-course-card',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './course-card.component.html',
  styleUrls: ['./course-card.component.scss']
})
export class CourseCardComponent implements OnInit {
  @Input() course: any;
  isInWishlist = false;

  constructor(
    private wishlistService: WishlistService,
    public authService: AuthService,
    private toastService: ToastService
  ) {}

  ngOnInit() {
    this.wishlistService.wishlist$.subscribe(ids => {
      if (this.course) {
        this.isInWishlist = ids.includes(this.course.id);
      }
    });
  }

  toggleWishlist(event: Event) {
    event.stopPropagation();
    event.preventDefault();

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
}
