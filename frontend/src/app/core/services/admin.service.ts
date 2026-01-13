import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AdminService {
  private apiUrl = '/api/admin';

  constructor(private http: HttpClient) {}

  // Dashboard
  getDashboardStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/dashboard/stats`);
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
}
