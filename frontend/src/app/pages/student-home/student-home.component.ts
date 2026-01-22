import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CourseService } from '../../services/course.service';
import { AuthService } from '../../services/auth.service';
import { CourseCardComponent } from '../../shared/components/course-card/course-card.component';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-student-home',
  standalone: true,
  imports: [CommonModule, CourseCardComponent, RouterModule],
  templateUrl: './student-home.component.html',
  styleUrls: ['./student-home.component.scss']
})
export class StudentHomeComponent implements OnInit {
  recommendedCourses: any[] = [];
  popularCourses: any[] = [];
  userName: string = 'Student'; // Placeholder

  constructor(
    private courseService: CourseService,
    private authService: AuthService
  ) {}

  ngOnInit() {
    this.loadData();
    const user = this.authService.getUser();
    if (user) {
      this.userName = user.name || 'Student';
    } else {
      this.userName = 'Guest';
    }
  }

  loadData() {
    this.courseService.getRecommendedCourses().subscribe({
      next: (data: any) => this.recommendedCourses = data.data || data,
      error: (err) => console.error('Error loading recommended courses', err)
    });

    this.courseService.getPopularCourses().subscribe({
      next: (data: any) => this.popularCourses = data.data || data,
      error: (err) => console.error('Error loading popular courses', err)
    });
  }
}
