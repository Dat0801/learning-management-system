import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject } from 'rxjs';
import { tap } from 'rxjs/operators';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = environment.apiUrl;
  private userSubject: BehaviorSubject<any>;
  public user$: Observable<any>;

  constructor(private http: HttpClient) {
    this.userSubject = new BehaviorSubject<any>(this.getUserFromStorage());
    this.user$ = this.userSubject.asObservable();
  }

  register(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, data).pipe(
        tap((res: any) => {
          this.setToken(res.access_token);
          this.setUser(res.user);
        })
    );
  }

  login(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, data).pipe(
      tap((res: any) => {
        this.setToken(res.access_token);
        this.setUser(res.user);
      })
    );
  }

  logout(): void {
    // Optional: Call backend logout endpoint if available
    // this.http.post(`${this.apiUrl}/logout`, {}).subscribe();
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    this.userSubject.next(null);
  }

  private setToken(token: string) {
    localStorage.setItem('token', token);
  }

  private setUser(user: any) {
    localStorage.setItem('user', JSON.stringify(user));
    this.userSubject.next(user);
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }

  private getUserFromStorage(): any {
    const userStr = localStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
  }

  getUser(): any {
    return this.userSubject.value;
  }

  isLoggedIn(): boolean {
    return !!this.getToken();
  }
}
