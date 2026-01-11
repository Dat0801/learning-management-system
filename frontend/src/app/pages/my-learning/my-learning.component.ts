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
  `,
  styles: [`
    .page-header {
      margin-bottom: 2rem;
      h1 { font-size: 2rem; color: #2d3748; margin-bottom: 0.5rem; }
      p { color: #718096; }
    }
    .course-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 2rem;
    }
    .empty-state {
      text-align: center;
      padding: 4rem 0;
      p { color: #718096; margin-bottom: 1.5rem; }
      .btn-primary {
        display: inline-block;
        background: #3182ce;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.375rem;
        text-decoration: none;
        transition: background 0.2s;
        &:hover { background: #2c5282; }
      }
    }
  `]
})
export class MyLearningComponent implements OnInit {
  courses: any[] = [];
  isLoading = true;

  constructor(private courseService: CourseService) {}

  ngOnInit() {
    this.courseService.getMyCourses().subscribe({
      next: (data) => {
        this.courses = data;
        this.isLoading = false;
      },
      error: (err) => {
        console.error('Error loading enrolled courses', err);
        this.isLoading = false;
      }
    });
  }
}
