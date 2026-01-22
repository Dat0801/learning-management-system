import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AdminService {
  private apiUrl = `${environment.apiUrl}/admin`;

  constructor(private http: HttpClient) {}

  // Dashboard
  getDashboardStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/dashboard/stats`);
  }

  // Categories
  getAllCategories(): Observable<any> {
    return this.http.get(`${environment.apiUrl}/categories`);
  }

  createCategory(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/categories`, data);
  }

  updateCategory(categoryId: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/categories/${categoryId}`, data);
  }

  deleteCategory(categoryId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/categories/${categoryId}`);
  }

  // Users
  getAllUsers(search: string = '', role: string = ''): Observable<any> {
    let url = `${this.apiUrl}/users`;
    const params: any = {};
    
    if (search) params.search = search;
    if (role) params.role = role;

    return this.http.get(url, { params });
  }

  getUserDetail(userId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/users/${userId}`);
  }

  createUser(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/users`, data);
  }

  updateUser(userId: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/users/${userId}`, data);
  }

  deleteUser(userId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/users/${userId}`);
  }

  // Courses
  getAllCoursesAdmin(search: string = '', status: string = ''): Observable<any> {
    let url = `${this.apiUrl}/courses`;
    const params: any = {};
    
    if (search) params.search = search;
    if (status) params.status = status;

    return this.http.get(url, { params });
  }

  getCourseDetail(courseId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/courses/${courseId}`);
  }

  createCourseAdmin(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/courses`, data);
  }

  updateCourseAdmin(courseId: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/courses/${courseId}`, data);
  }

  deleteCourseAdmin(courseId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/courses/${courseId}`);
  }

  // Enrollments
  getAllEnrollments(status: string = ''): Observable<any> {
    let url = `${this.apiUrl}/enrollments`;
    const params: any = {};
    
    if (status) params.status = status;

    return this.http.get(url, { params });
  }

  deleteEnrollment(enrollmentId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/enrollments/${enrollmentId}`);
  }

  createEnrollment(userId: number, courseId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/enrollments`, { user_id: userId, course_id: courseId });
  }

  // Lessons
  getLessons(courseId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/courses/${courseId}/lessons`);
  }

  createLesson(courseId: number, data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/courses/${courseId}/lessons`, data);
  }

  updateLesson(courseId: number, lessonId: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/courses/${courseId}/lessons/${lessonId}`, data);
  }

  deleteLesson(courseId: number, lessonId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/courses/${courseId}/lessons/${lessonId}`);
  }

  // Quiz Management
  getQuiz(lessonId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/lessons/${lessonId}/quiz`);
  }

  createOrUpdateQuiz(lessonId: number, data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/lessons/${lessonId}/quiz`, data);
  }

  deleteQuiz(quizId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/quizzes/${quizId}`);
  }

  createQuestion(quizId: number, data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/quizzes/${quizId}/questions`, data);
  }

  updateQuestion(questionId: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/questions/${questionId}`, data);
  }

  deleteQuestion(questionId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/questions/${questionId}`);
  }

  createAnswer(questionId: number, data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/questions/${questionId}/answers`, data);
  }

  updateAnswer(answerId: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/answers/${answerId}`, data);
  }

  deleteAnswer(answerId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/answers/${answerId}`);
  }
}
