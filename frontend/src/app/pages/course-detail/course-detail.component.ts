import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { CourseService } from '../../services/course.service';
import { AuthService } from '../../services/auth.service';
import { Course, Lesson } from '../../models/course.model';
import { map, switchMap } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
  selector: 'app-course-detail',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './course-detail.component.html',
  styleUrls: ['./course-detail.component.scss']
})
export class CourseDetailComponent implements OnInit {
  course: Course | null = null;
  loading = true;
  currentLesson: Lesson | null = null;

  constructor(
    private route: ActivatedRoute,
    private courseService: CourseService,
    public authService: AuthService
  ) {}

  ngOnInit(): void {
    this.route.paramMap.pipe(
      switchMap(params => {
        const id = params.get('id');
        if (id) {
          return this.courseService.getCourse(+id);
        }
        return of(null);
      })
    ).subscribe({
      next: (course) => {
        this.course = course;
        this.determineLessonStatus();
        this.loading = false;
      },
      error: (err) => {
        console.error('Failed to load course', err);
        this.loading = false;
      }
    });
  }

  determineLessonStatus() {
    if (!this.course || !this.course.lessons) return;

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

  getLessonStatus(lesson: Lesson): 'completed' | 'current' | 'locked' {
    if (!this.course?.is_enrolled) {
      // Maybe allow preview of first lesson?
      // return lesson.order === 1 ? 'current' : 'locked';
      return 'locked';
    }

    if (lesson.is_completed) {
      return 'completed';
    }

    if (this.currentLesson && lesson.id === this.currentLesson.id) {
      return 'current';
    }

    return 'locked';
  }

  enroll() {
    if (!this.course) return;
    
    if (!this.authService.isLoggedIn()) {
      alert('Please login to enroll');
      // Redirect to login
      return;
    }

    this.courseService.enroll(this.course.id).subscribe({
      next: () => {
        alert('Enrolled successfully!');
        // Refresh course data
        if (this.course) {
            this.course.is_enrolled = true;
            this.determineLessonStatus();
        }
      },
      error: (err) => {
        alert('Failed to enroll: ' + (err.error?.message || err.message));
      }
    });
  }
}
