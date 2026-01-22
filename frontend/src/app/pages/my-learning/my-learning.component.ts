import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CourseService } from '../../services/course.service';
import { CourseCardComponent } from '../../shared/components/course-card/course-card.component';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-my-learning',
  standalone: true,
  imports: [CommonModule, CourseCardComponent, RouterModule],
  template: `
    <div class="page-container my-learning-page">
      <div class="page-header">
        <h1>My Learning</h1>
        <p>Continue learning where you left off</p>
      </div>

      <div class="loading-spinner" *ngIf="isLoading">
        Loading your courses...
      </div>

      <div class="empty-state" *ngIf="!isLoading && courses.length === 0">
        <p>You haven't enrolled in any courses yet.</p>
        <a routerLink="/courses" class="btn-primary">Browse Courses</a>
      </div>

      <div class="course-grid" *ngIf="!isLoading && courses.length > 0">
        <app-course-card *ngFor="let course of courses" [course]="course"></app-course-card>
      </div>
    </div>
  `,
  styles: [`
    .course-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 2rem;
    }
    .empty-state {
      text-align: center;
      padding: 4rem 0;
      p { color: var(--text-muted); margin-bottom: 1.5rem; }
    }
  `]
})
export class MyLearningComponent implements OnInit {
  courses: any[] = [];
  isLoading = true;

  constructor(private courseService: CourseService) {}

  ngOnInit() {
    this.courseService.getMyCourses().subscribe({
      next: (data: any) => {
        this.courses = data.data || data;
        this.isLoading = false;
      },
      error: (err) => {
        console.error('Error loading enrolled courses', err);
        this.isLoading = false;
      }
    });
  }
}
