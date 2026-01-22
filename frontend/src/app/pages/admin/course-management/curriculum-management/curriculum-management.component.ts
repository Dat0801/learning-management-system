import { Component, Input, OnInit, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../../core/services/admin.service';
import { ToastService } from '../../../../services/toast.service';
import { QuizManagementComponent } from '../quiz-management/quiz-management.component';

@Component({
  selector: 'app-curriculum-management',
  standalone: true,
  imports: [CommonModule, FormsModule, QuizManagementComponent],
  templateUrl: './curriculum-management.component.html',
  styleUrl: './curriculum-management.component.scss'
})
export class CurriculumManagementComponent implements OnInit, OnChanges {
  @Input() courseId: number | null = null;
  lessons: any[] = [];
  isLoading = false;
  
  editingLesson: any = null;
  isEditing = false;
  
  selectedLessonForQuiz: any = null;

  constructor(
    private adminService: AdminService,
    private toastService: ToastService
  ) {}

  ngOnInit() {
    if (this.courseId) {
      this.loadLessons();
    }
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['courseId'] && this.courseId) {
      this.loadLessons();
    }
  }

  loadLessons() {
    if (!this.courseId) return;
    this.isLoading = true;
    this.adminService.getLessons(this.courseId).subscribe({
      next: (data) => {
        this.lessons = data;
        this.isLoading = false;
      },
      error: (err) => {
        console.error('Error loading lessons', err);
        this.isLoading = false;
      }
    });
  }

  startAddLesson() {
    this.editingLesson = {
      title: '',
      content: '',
      video_url: '',
      duration: '',
      is_preview: false,
      order: this.lessons.length + 1
    };
    this.isEditing = true;
  }

  startEditLesson(lesson: any) {
    this.editingLesson = { ...lesson };
    this.isEditing = true;
  }

  cancelEdit() {
    this.editingLesson = null;
    this.isEditing = false;
  }

  saveLesson() {
    if (!this.courseId || !this.editingLesson) return;

    const request$ = this.editingLesson.id
      ? this.adminService.updateLesson(this.courseId, this.editingLesson.id, this.editingLesson)
      : this.adminService.createLesson(this.courseId, this.editingLesson);

    request$.subscribe({
      next: () => {
        this.loadLessons();
        this.cancelEdit();
        this.toastService.success('Lesson saved successfully');
      },
      error: (err) => {
        console.error('Error saving lesson', err);
        this.toastService.error('Failed to save lesson');
      }
    });
  }

  deleteLesson(lesson: any) {
    if (!this.courseId || !confirm('Delete this lesson?')) return;
    
    this.adminService.deleteLesson(this.courseId, lesson.id).subscribe({
      next: () => {
        this.loadLessons();
        this.toastService.success('Lesson deleted successfully');
      },
      error: (err) => {
        console.error('Error deleting lesson', err);
        this.toastService.error('Failed to delete lesson');
      }
    });
  }

  manageQuiz(lesson: any) {
    this.selectedLessonForQuiz = lesson;
  }

  closeQuiz() {
    this.selectedLessonForQuiz = null;
  }
}
