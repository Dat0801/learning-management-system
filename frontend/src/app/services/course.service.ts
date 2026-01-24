import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class CourseService {
  private apiUrl = `${environment.apiUrl}/courses`;

  constructor(private http: HttpClient) { }

  getCourses(filters: any = {}): Observable<any[]> {
    return this.http.get<any[]>(this.apiUrl, { params: filters });
  }

  getCategories(): Observable<any[]> {
    return this.http.get<any[]>(`${environment.apiUrl}/categories`);
  }

  getCategory(slug: string): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/categories/${slug}`);
  }

  getRecommendedCourses(): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/recommended`);
  }

  getPopularCourses(): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/popular`);
  }

  getCourse(id: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/${id}`);
  }

  createCourse(data: any): Observable<any> {
    return this.http.post(this.apiUrl, data);
  }

  enroll(courseId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/${courseId}/enroll`, {});
  }

  getMyCourses(): Observable<any[]> {
    return this.http.get<any[]>(`${environment.apiUrl}/my-courses`);
  }

  getLesson(lessonId: number): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/lessons/${lessonId}`);
  }

  completeLesson(lessonId: number): Observable<any> {
    return this.http.post(`${environment.apiUrl}/lessons/${lessonId}/complete`, {});
  }

  incompleteLesson(lessonId: number): Observable<any> {
    return this.http.delete(`${environment.apiUrl}/lessons/${lessonId}/complete`);
  }

  getReviews(courseId: number): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/${courseId}/reviews`);
  }

  createReview(courseId: number, data: { rating: number; comment: string }): Observable<any> {
    return this.http.post(`${this.apiUrl}/${courseId}/reviews`, data);
  }

  deleteReview(reviewId: number): Observable<any> {
    return this.http.delete(`${environment.apiUrl}/reviews/${reviewId}`);
  }

  getQuizByLesson(lessonId: number): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/lessons/${lessonId}/quiz`);
  }

  submitQuiz(quizId: number, answers: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/quizzes/${quizId}/submit`, { answers });
  }

  // Lesson Resources
  getLessonResources(lessonId: number): Observable<any[]> {
    return this.http.get<any[]>(`${environment.apiUrl}/lessons/${lessonId}/resources`);
  }

  // Lesson Notes
  getLessonNote(lessonId: number): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/lessons/${lessonId}/note`);
  }

  saveLessonNote(lessonId: number, content: string): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/lessons/${lessonId}/note`, { content });
  }

  // Lesson Q&A
  getLessonQuestions(lessonId: number): Observable<any[]> {
    return this.http.get<any[]>(`${environment.apiUrl}/lessons/${lessonId}/questions`);
  }

  askQuestion(lessonId: number, data: { title: string, content: string }): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/lessons/${lessonId}/questions`, data);
  }

  answerQuestion(questionId: number, content: string): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/questions/${questionId}/answers`, { content });
  }
}
