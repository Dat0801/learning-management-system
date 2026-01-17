import { Component, OnInit, OnDestroy } from '@angular/core';
import { RouterModule, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-student-layout',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './student-layout.component.html',
  styleUrl: './student-layout.component.scss'
})
export class StudentLayoutComponent implements OnInit, OnDestroy {
  isMenuOpen = false;
  isUserDropdownOpen = false;
  user: any = null;
  searchQuery = '';
  private userSub: Subscription | undefined;

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit() {
    this.userSub = this.authService.user$.subscribe(user => {
      this.user = user;
    });
  }

  ngOnDestroy() {
    if (this.userSub) {
      this.userSub.unsubscribe();
    }
  }

  toggleMenu() {
    this.isMenuOpen = !this.isMenuOpen;
  }

  toggleUserDropdown() {
    this.isUserDropdownOpen = !this.isUserDropdownOpen;
  }

  submitSearch() {
    const term = this.searchQuery.trim();
    const extras: any = {};

    if (term) {
      extras.queryParams = { q: term };
    }

    this.router.navigate(['/courses'], extras);
    this.isMenuOpen = false;
    this.isUserDropdownOpen = false;
  }

  logout() {
    this.authService.logout();
    this.router.navigate(['/auth/login']);
    this.isMenuOpen = false;
    this.isUserDropdownOpen = false;
  }
}
