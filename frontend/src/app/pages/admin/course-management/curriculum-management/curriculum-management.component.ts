import { Component, Input, OnInit, OnChanges, SimpleChanges, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../../core/services/admin.service';
import { ToastService } from '../../../../services/toast.service';
import { QuizManagementComponent } from '../quiz-management/quiz-management.component';
import { ResourceManagementComponent } from '../resource-management/resource-management.component';

@Component({
  selector: 'app-curriculum-management',
  standalone: true,
  imports: [CommonModule, FormsModule, QuizManagementComponent, ResourceManagementComponent],
  templateUrl: './curriculum-management.component.html',
  styleUrl: './curriculum-management.component.scss'
})
export class CurriculumManagementComponent implements OnInit, OnChanges {
  @Input() courseId: number | null = null;
  @Input() lessonsList: any[] = [];
  @Output() lessonsChange = new EventEmitter<any[]>();
  
  lessons: any[] = [];
  isLoading = false;
  
  editingLesson: any = null;
  isEditing = false;
  
  selectedLessonForQuiz: any = null;
  selectedLessonForResources: any = null;

  constructor(
    private adminService: AdminService,
    private toastService: ToastService
  ) {}

  ngOnInit() {
    this.loadLessons();
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['courseId']) {
      this.loadLessons();
    }
    if (changes['lessonsList'] && !this.courseId) {
      // Only update from input if we are in local mode (no courseId)
      // and prevent overwriting if we have local changes? 
      // Actually, standard pattern is to sync.
      this.lessons = [...(this.lessonsList || [])];
    }
  }

  loadLessons() {
    if (this.courseId) {
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
    } else {
      // Local mode
      this.lessons = [...(this.lessonsList || [])];
    }
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
    if (!this.editingLesson) return;

    if (this.courseId) {
      // API Mode
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
    } else {
      // Local Mode
      // Assign temp ID if new
      if (!this.editingLesson.id && !this.editingLesson._tempId) {
        this.editingLesson._tempId = Date.now();
      }

      const index = this.lessons.findIndex(l => 
        (l.id && l.id === this.editingLesson.id) || 
        (l._tempId && l._tempId === this.editingLesson._tempId)
      );

      if (index > -1) {
        this.lessons[index] = this.editingLesson;
      } else {
        this.lessons.push(this.editingLesson);
      }

      this.lessonsChange.emit(this.lessons);
      this.cancelEdit();
      this.toastService.success('Lesson added to list (unsaved)');
    }
  }

  deleteLesson(lesson: any) {
    if (this.courseId) {
      if (!confirm('Delete this lesson?')) return;
      
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
    } else {
      // Local Mode
      if (!confirm('Remove this lesson from list?')) return;
      
      this.lessons = this.lessons.filter(l => 
        (l.id && l.id !== lesson.id) || 
        (l._tempId && l._tempId !== lesson._tempId)
      );
      
      this.lessonsChange.emit(this.lessons);
      this.toastService.success('Lesson removed from list');
    }
  }

  manageQuiz(lesson: any) {
    if (!this.courseId) {
      this.toastService.info('Please save the course first to manage quizzes.');
      return;
    }
    this.selectedLessonForQuiz = lesson;
  }

  closeQuiz() {
    this.selectedLessonForQuiz = null;
  }

  manageResources(lesson: any) {
    if (!this.courseId) {
      this.toastService.info('Please save the course first to manage resources.');
      return;
    }
    this.selectedLessonForResources = lesson;
  }

  closeResources() {
    this.selectedLessonForResources = null;
  }
}
