import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { CourseService } from '../../services/course.service';
import { ToastService } from '../../services/toast.service';
import { Course, Lesson } from '../../models/course.model';
import { switchMap, tap } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
  selector: 'app-learning',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './learning.component.html',
  styleUrls: ['./learning.component.scss']
})
export class LearningComponent implements OnInit {
  course: Course | null = null;
  currentLesson: Lesson | null = null;
  loading = true;
  progress = 0;
  sidebarOpen = true;

  // Video State
  safeVideoUrl: SafeResourceUrl | null = null;
  isYoutube = false;

  // UI State
  activeTab: 'overview' | 'resources' | 'notes' | 'qa' = 'overview';


  // Quiz State
  quiz: any = null;
  quizLoading = false;
  quizSubmitted = false;
  quizResult: any = null;
  userAnswers: { [key: number]: number } = {};

  // Resources State
  resources: any[] = [];
  resourcesLoading = false;

  // Notes State
  noteContent = '';
  noteLoading = false;
  noteSaving = false;

  // Q&A State
  questions: any[] = [];
  questionsLoading = false;
  newQuestion = { title: '', content: '' };
  newAnswer: { [key: number]: string } = {};
  submittingQuestion = false;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private courseService: CourseService,
    private toastService: ToastService,
    private sanitizer: DomSanitizer
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
      next: (response: any) => {
        const course = response.data || response;
        if (!course) {
          this.router.navigate(['/home']);
          return;
        }
        this.course = course;
        this.calculateProgress();
        
        // Determine lesson to show
        const lessonId = this.route.snapshot.queryParamMap.get('lesson');
        if (lessonId) {
          const lesson = this.course?.lessons?.find(l => l.id === +lessonId);
          if (lesson) {
             this.selectLesson(lesson);
          }
        } 
        
        if (!this.currentLesson && this.course?.lessons?.length) {
            // Default to first incomplete or first lesson
            // If not enrolled, default to first preview lesson
            let defaultLesson: Lesson | undefined;
            
            if (this.course.is_enrolled) {
                defaultLesson = this.course.lessons.find(l => !l.is_completed) || this.course.lessons[0];
            } else {
                defaultLesson = this.course.lessons.find(l => l.is_preview);
            }
            
            if (defaultLesson) {
                this.selectLesson(defaultLesson);
            }
        }

        this.loading = false;
      },
      error: (err) => {
        console.error('Failed to load course', err);
        this.router.navigate(['/home']);
      }
    });
  }

  isLessonAccessible(lesson: Lesson): boolean {
    if (this.course?.is_enrolled) return true;
    return !!lesson.is_preview;
  }

  selectLesson(lesson: Lesson) {
    if (!this.isLessonAccessible(lesson)) return;

    this.currentLesson = lesson;
    
    // Reset Quiz State
    this.quiz = null;
    this.quizLoading = false;
    this.quizSubmitted = false;
    this.quizResult = null;
    this.userAnswers = {};

    // Reset Tab Data
    this.resources = [];
    this.noteContent = '';
    this.questions = [];

    if (this.activeTab === 'resources') this.loadResources();
    if (this.activeTab === 'notes') this.loadNote();
    if (this.activeTab === 'qa') this.loadQuestions();

    if (lesson.has_quiz) {
      this.loadQuiz(lesson.id);
    }

    // Process Video URL
    if (lesson.video_url) {
      this.processVideoUrl(lesson.video_url);
    } else {
      this.safeVideoUrl = null;
      this.isYoutube = false;
    }

    this.updateUrl();
    window.scrollTo(0, 0);
  }

  processVideoUrl(url: string) {
    // Regex to match YouTube video ID from various URL formats (including mobile, short links, etc.)
    const youtubeRegex = /(?:https?:\/\/)?(?:www\.|m\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})(?:\S+)?/;
    const match = url.match(youtubeRegex);

    if (match && match[1]) {
      this.isYoutube = true;
      const videoId = match[1];
      // Add origin for security and rel=0 to limit related videos
      const origin = window.location.origin;
      const embedUrl = `https://www.youtube.com/embed/${videoId}?origin=${origin}&rel=0`;
      this.safeVideoUrl = this.sanitizer.bypassSecurityTrustResourceUrl(embedUrl);
    } else {
      this.isYoutube = false;
      this.safeVideoUrl = null; // Let <video> tag handle it or use trusted resource url if needed
    }
  }

  loadQuiz(lessonId: number) {
    this.quizLoading = true;
    this.courseService.getQuizByLesson(lessonId).subscribe({
      next: (res) => {
        if (res.success) {
            this.quiz = res.data;
        }
        this.quizLoading = false;
      },
      error: (err) => {
        console.error('Error loading quiz', err);
        this.quizLoading = false;
      }
    });
  }

  submitQuiz() {
    if (!this.quiz) return;
    
    const answers = this.userAnswers;
    
    this.courseService.submitQuiz(this.quiz.id, answers).subscribe({
      next: (res) => {
        this.quizResult = res.data;
        this.quizSubmitted = true;
        
        // Mark lesson as completed if passed
        if (this.quizResult.score >= this.quiz.passing_score && this.currentLesson && !this.currentLesson.is_completed) {
           this.markLessonComplete(this.currentLesson.id);
        }
      },
      error: (err) => this.toastService.error('Failed to submit quiz')
    });
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

    if (this.currentLesson.is_completed) {
        this.courseService.incompleteLesson(this.currentLesson.id).subscribe({
            next: (res) => {
                if (this.currentLesson) {
                    this.currentLesson.is_completed = false;
                    this.calculateProgress();
                }
            }
        });
    } else {
        this.markLessonComplete(this.currentLesson.id);
    }
  }

  markLessonComplete(lessonId: number) {
      this.courseService.completeLesson(lessonId).subscribe({
        next: (res) => {
            if (this.currentLesson && this.currentLesson.id === lessonId) {
                this.currentLesson.is_completed = true;
                this.calculateProgress();
            }
            // If passed via other means (quiz), update model
            if (this.course?.lessons) {
                const l = this.course.lessons.find(l => l.id === lessonId);
                if (l) l.is_completed = true;
                this.calculateProgress();
            }
        }
      });
  }

  calculateProgress() {
    if (!this.course?.lessons?.length) {
      this.progress = 0;
      return;
    }
    const completed = this.course.lessons.filter(l => l.is_completed).length;
    this.progress = Math.round((completed / this.course.lessons.length) * 100);
  }

  setActiveTab(tab: 'overview' | 'resources' | 'notes' | 'qa') {
    this.activeTab = tab;
    if (this.currentLesson) {
      if (tab === 'resources') this.loadResources();
      if (tab === 'notes') this.loadNote();
      if (tab === 'qa') this.loadQuestions();
    }
  }

  loadResources() {
    if (!this.currentLesson) return;
    this.resourcesLoading = true;
    this.courseService.getLessonResources(this.currentLesson.id).subscribe({
      next: (res: any) => {
        this.resources = res;
        this.resourcesLoading = false;
      },
      error: () => this.resourcesLoading = false
    });
  }

  loadNote() {
    if (!this.currentLesson) return;
    this.noteLoading = true;
    this.courseService.getLessonNote(this.currentLesson.id).subscribe({
      next: (res: any) => {
        this.noteContent = res?.content || '';
        this.noteLoading = false;
      },
      error: () => this.noteLoading = false
    });
  }

  saveNote() {
    if (!this.currentLesson) return;
    this.noteSaving = true;
    this.courseService.saveLessonNote(this.currentLesson.id, this.noteContent).subscribe({
      next: () => {
        this.toastService.success('Note saved');
        this.noteSaving = false;
      },
      error: () => {
        this.toastService.error('Failed to save note');
        this.noteSaving = false;
      }
    });
  }

  loadQuestions() {
    if (!this.currentLesson) return;
    this.questionsLoading = true;
    this.courseService.getLessonQuestions(this.currentLesson.id).subscribe({
      next: (res: any) => {
        this.questions = res;
        this.questionsLoading = false;
      },
      error: () => this.questionsLoading = false
    });
  }

  askQuestion() {
    if (!this.currentLesson || !this.newQuestion.title || !this.newQuestion.content) return;
    this.submittingQuestion = true;
    this.courseService.askQuestion(this.currentLesson.id, this.newQuestion).subscribe({
      next: (question) => {
        this.questions.unshift(question);
        this.newQuestion = { title: '', content: '' };
        this.submittingQuestion = false;
        this.toastService.success('Question posted');
      },
      error: () => {
        this.toastService.error('Failed to post question');
        this.submittingQuestion = false;
      }
    });
  }

  submitAnswer(questionId: number) {
    if (!this.newAnswer[questionId]) return;
    this.courseService.answerQuestion(questionId, this.newAnswer[questionId]).subscribe({
      next: (answer) => {
        const question = this.questions.find(q => q.id === questionId);
        if (question) {
          if (!question.answers) question.answers = [];
          question.answers.push(answer);
        }
        this.newAnswer[questionId] = '';
        this.toastService.success('Answer posted');
      },
      error: () => this.toastService.error('Failed to post answer')
    });
  }

  toggleSidebar() {
    this.sidebarOpen = !this.sidebarOpen;
  }

  prevLesson() {
    if (!this.course?.lessons || !this.currentLesson) return;
    const currentIndex = this.course.lessons.findIndex(l => l.id === this.currentLesson!.id);
    if (currentIndex > 0) {
      this.selectLesson(this.course.lessons[currentIndex - 1]);
    }
  }

  nextLesson() {
    if (!this.course?.lessons || !this.currentLesson) return;
    const currentIndex = this.course.lessons.findIndex(l => l.id === this.currentLesson!.id);
    if (currentIndex < this.course.lessons.length - 1) {
      this.selectLesson(this.course.lessons[currentIndex + 1]);
    }
  }
}
