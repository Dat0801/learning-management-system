import { Component, Input, OnInit, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../../../../core/services/admin.service';
import { ToastService } from '../../../../services/toast.service';

@Component({
  selector: 'app-quiz-management',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './quiz-management.component.html',
  styleUrl: './quiz-management.component.scss'
})
export class QuizManagementComponent implements OnInit, OnChanges {
  @Input() lessonId: number | null = null;
  quiz: any = null;
  isLoading = false;
  isEditingQuiz = false;
  
  // Question Editing
  editingQuestion: any = null;
  isEditingQuestion = false;

  // Answer Editing
  editingAnswer: any = null;
  isEditingAnswer = false;

  constructor(
    private adminService: AdminService,
    private toastService: ToastService
  ) {}

  ngOnInit() {
    if (this.lessonId) {
      this.loadQuiz();
    }
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['lessonId'] && this.lessonId) {
      this.loadQuiz();
    }
  }

  loadQuiz() {
    if (!this.lessonId) return;
    this.isLoading = true;
    this.adminService.getQuiz(this.lessonId).subscribe({
      next: (data) => {
        this.quiz = data;
        this.isLoading = false;
      },
      error: (err) => {
        console.error('Error loading quiz', err);
        this.isLoading = false;
      }
    });
  }

  // Quiz Operations
  startCreateQuiz() {
    this.quiz = {
      title: 'Lesson Quiz',
      description: '',
      passing_score: 70,
      questions: []
    };
    this.isEditingQuiz = true;
  }

  startEditQuiz() {
    this.isEditingQuiz = true;
  }

  saveQuiz() {
    if (!this.lessonId || !this.quiz) return;
    
    this.adminService.createOrUpdateQuiz(this.lessonId, {
      title: this.quiz.title,
      description: this.quiz.description,
      passing_score: this.quiz.passing_score
    }).subscribe({
      next: (savedQuiz) => {
        // Preserve questions if updating
        const questions = this.quiz.questions;
        this.quiz = savedQuiz;
        this.quiz.questions = questions; 
        this.isEditingQuiz = false;
        
        // Reload to be safe
        this.loadQuiz();
        this.toastService.success('Quiz saved successfully');
      },
      error: (err) => {
        console.error('Error saving quiz', err);
        this.toastService.error('Failed to save quiz');
      }
    });
  }

  cancelEditQuiz() {
    this.isEditingQuiz = false;
    if (!this.quiz.id) {
      this.quiz = null; // Cancel creation
    }
  }

  deleteQuiz() {
    if (!this.quiz || !this.quiz.id || !confirm('Delete this quiz?')) return;
    
    this.adminService.deleteQuiz(this.quiz.id).subscribe({
      next: () => {
        this.quiz = null;
        this.toastService.success('Quiz deleted successfully');
      },
      error: (err) => {
        console.error('Error deleting quiz', err);
        this.toastService.error('Failed to delete quiz');
      }
    });
  }

  // Question Operations
  startAddQuestion() {
    this.editingQuestion = {
      question_text: '',
      order: (this.quiz.questions?.length || 0) + 1
    };
    this.isEditingQuestion = true;
  }

  startEditQuestion(question: any) {
    this.editingQuestion = { ...question };
    this.isEditingQuestion = true;
  }

  saveQuestion() {
    if (!this.quiz || !this.quiz.id) return;
    
    const request$ = this.editingQuestion.id
      ? this.adminService.updateQuestion(this.editingQuestion.id, this.editingQuestion)
      : this.adminService.createQuestion(this.quiz.id, this.editingQuestion);

    request$.subscribe({
      next: () => {
        this.isEditingQuestion = false;
        this.editingQuestion = null;
        this.loadQuiz(); // Reload to show new question
        this.toastService.success('Question saved successfully');
      },
      error: (err) => {
        console.error('Error saving question', err);
        this.toastService.error('Failed to save question');
      }
    });
  }

  deleteQuestion(question: any) {
    if (!confirm('Delete this question?')) return;
    
    this.adminService.deleteQuestion(question.id).subscribe({
      next: () => {
        this.loadQuiz();
        this.toastService.success('Question deleted successfully');
      },
      error: (err) => {
        console.error('Error deleting question', err);
        this.toastService.error('Failed to delete question');
      }
    });
  }

  cancelEditQuestion() {
    this.isEditingQuestion = false;
    this.editingQuestion = null;
  }

  // Answer Operations (Nested in Question Edit or separate?)
  // Let's make it so you edit answers within the Question Edit view?
  // Or maybe a separate view.
  // For simplicity, let's allow managing answers only when a question is selected/expanded.
  // But wait, the API structure is RESTful.
  
  // Let's do a simple UI: Question List. Click "Manage Answers" on a question?
  // Or show answers inline.

  addAnswer(question: any) {
    const answerText = prompt('Enter answer text:');
    if (!answerText) return;
    
    this.adminService.createAnswer(question.id, {
      answer_text: answerText,
      is_correct: false
    }).subscribe({
      next: () => {
        this.loadQuiz();
      },
      error: (err) => console.error(err)
    });
  }

  deleteAnswer(answer: any) {
    if (!confirm('Delete answer?')) return;
    this.adminService.deleteAnswer(answer.id).subscribe({
      next: () => this.loadQuiz(),
      error: (err) => console.error(err)
    });
  }

  toggleCorrect(answer: any) {
    this.adminService.updateAnswer(answer.id, {
      is_correct: !answer.is_correct
    }).subscribe({
      next: () => this.loadQuiz(),
      error: (err) => console.error(err)
    });
  }
}
