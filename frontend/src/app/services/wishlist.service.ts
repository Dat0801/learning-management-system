import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject } from 'rxjs';
import { tap } from 'rxjs/operators';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class WishlistService {
  private apiUrl = `${environment.apiUrl}/wishlist`;
  private wishlistSubject = new BehaviorSubject<number[]>([]);
  public wishlist$ = this.wishlistSubject.asObservable();

  constructor(private http: HttpClient) {
    this.loadWishlist();
  }

  loadWishlist() {
    this.getWishlist().subscribe({
      next: (courses) => {
        const ids = courses.map((c: any) => c.id);
        this.wishlistSubject.next(ids);
      },
      error: () => {
        // Handle error (e.g., user not logged in)
        this.wishlistSubject.next([]);
      }
    });
  }

  getWishlist(): Observable<any[]> {
    return this.http.get<any[]>(this.apiUrl);
  }

  addToWishlist(courseId: number): Observable<any> {
    return this.http.post(this.apiUrl, { course_id: courseId }).pipe(
      tap(() => {
        const current = this.wishlistSubject.value;
        if (!current.includes(courseId)) {
          this.wishlistSubject.next([...current, courseId]);
        }
      })
    );
  }

  removeFromWishlist(courseId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${courseId}`).pipe(
      tap(() => {
        const current = this.wishlistSubject.value;
        this.wishlistSubject.next(current.filter(id => id !== courseId));
      })
    );
  }

  isInWishlist(courseId: number): boolean {
    return this.wishlistSubject.value.includes(courseId);
  }
}
