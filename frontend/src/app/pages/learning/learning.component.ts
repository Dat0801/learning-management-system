import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { CourseService } from '../../services/course.service';
import { Course, Lesson } from '../../models/course.model';
import { switchMap, tap } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
  selector: 'app-learning',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './learning.component.html',
  styleUrls: ['./learning.component.scss']
})
export class LearningComponent implements OnInit {
  course: Course | null = null;
  currentLesson: Lesson | null = null;
  loading = true;
  progress = 0;
  sidebarOpen = true;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private courseService: CourseService
  ) {}

  ngOnInit(): void {
    this.route.paramMap.pipe(
      switchMap(params => {
        const courseId = params.get('courseId');
        if (courseId) {
          return this.courseService.getCourse(+courseId);
        }
        return of(null);
      })
    ).subscribe({
      next: (course) => {
        if (!course) {
          this.router.navigate(['/home']);
          return;
        }
        this.course = course;
        this.calculateProgress();
        
        // Determine lesson to show
        const lessonId = this.route.snapshot.queryParamMap.get('lesson');
        if (lessonId) {
          this.currentLesson = this.course?.lessons?.find(l => l.id === +lessonId) || null;
        } 
        
        if (!this.currentLesson && this.course?.lessons?.length) {
            // Default to first incomplete or first lesson
            this.currentLesson = this.course.lessons.find(l => !l.is_completed) || this.course.lessons[0];
            // Update URL without reload
            this.updateUrl();
        }

        this.loading = false;
      },
      error: (err) => {
        console.error('Failed to load course', err);
        this.router.navigate(['/home']);
      }
    });
  }

  selectLesson(lesson: Lesson) {
    this.currentLesson = lesson;
    this.updateUrl();
    window.scrollTo(0, 0);
  }

  updateUrl() {
    if (this.course && this.currentLesson) {
      this.router.navigate([], {
        relativeTo: this.route,
        queryParams: { lesson: this.currentLesson.id },
        queryParamsHandling: 'merge',
        replaceUrl: true
      });
    }
  }

  toggleCompletion() {
    if (!this.currentLesson) return;

    const action = this.currentLesson.is_completed 
      ? this.courseService.incompleteLesson(this.currentLesson.id)
      : this.courseService.completeLesson(this.currentLesson.id);

    action.subscribe({
      next: (res) => {
        if (this.currentLesson) {
            this.currentLesson.is_completed = res.is_completed;
            this.calculateProgress();
        }
      },
      error: (err) => alert('Failed to update status')
    });
  }

  calculateProgress() {
    if (!this.course || !this.course.lessons || this.course.lessons.length === 0) {
      this.progress = 0;
      return;
    }
    const completedCount = this.course.lessons.filter(l => l.is_completed).length;
    this.progress = Math.round((completedCount / this.course.lessons.length) * 100);
  }

  toggleSidebar() {
    this.sidebarOpen = !this.sidebarOpen;
  }

  nextLesson() {
    if (!this.course || !this.course.lessons || !this.currentLesson) return;
    const currentIndex = this.course.lessons.findIndex(l => l.id === this.currentLesson!.id);
    if (currentIndex < this.course.lessons.length - 1) {
      this.selectLesson(this.course.lessons[currentIndex + 1]);
    }
  }

  prevLesson() {
    if (!this.course || !this.course.lessons || !this.currentLesson) return;
    const currentIndex = this.course.lessons.findIndex(l => l.id === this.currentLesson!.id);
    if (currentIndex > 0) {
      this.selectLesson(this.course.lessons[currentIndex - 1]);
    }
  }
}
