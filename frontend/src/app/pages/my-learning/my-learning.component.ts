import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CourseService } from '../../services/course.service';
import { AuthService } from '../../services/auth.service';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-my-learning',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './my-learning.component.html',
  styleUrls: ['./my-learning.component.scss']
})
export class MyLearningComponent implements OnInit {
  courses: any[] = [];
  filteredCourses: any[] = [];
  isLoading = true;
  activeTab: 'active' | 'completed' = 'active';
  searchQuery: string = '';

  constructor(
    private courseService: CourseService,
    public authService: AuthService
  ) {}

  ngOnInit() {
    this.courseService.getMyCourses().subscribe({
      next: (data: any) => {
        const enrollments = data.data || data;
        this.courses = enrollments.map((enrollment: any) => {
          const course = enrollment.course;
          course.is_enrolled = true;
          // Use enrollment progress if available, otherwise default to course progress or 0
          course.progress = enrollment.progress !== undefined ? enrollment.progress : (course.progress || 0);
          
          // If we have completed_lessons_count and lessons_count, we can calculate progress
          if (course.lessons_count > 0 && course.completed_lessons_count !== undefined) {
             course.progress = Math.round((course.completed_lessons_count / course.lessons_count) * 100);
          }
          
          // Mock data for demo purposes if not available
          if (course.progress === undefined) course.progress = Math.floor(Math.random() * 100);
          if (!course.lessons_count) course.lessons_count = 20 + Math.floor(Math.random() * 30);
          if (!course.completed_lessons_count) course.completed_lessons_count = Math.floor((course.progress / 100) * course.lessons_count);
          if (!course.last_accessed) {
            // Random date within last 30 days
            const date = new Date();
            date.setDate(date.getDate() - Math.floor(Math.random() * 30));
            course.last_accessed = date;
          }
          
          return course;
        });
        this.filterCourses();
        this.isLoading = false;
      },
      error: (err) => {
        console.error('Error loading enrolled courses', err);
        this.isLoading = false;
      }
    });
  }

  setActiveTab(tab: 'active' | 'completed') {
    this.activeTab = tab;
    this.filterCourses();
  }

  filterCourses() {
    let filtered = this.courses;

    // Filter by tab
    if (this.activeTab === 'active') {
      filtered = filtered.filter(course => (course.progress || 0) < 100);
    } else {
      filtered = filtered.filter(course => (course.progress || 0) === 100);
    }

    // Filter by search query
    if (this.searchQuery.trim()) {
      const query = this.searchQuery.toLowerCase();
      filtered = filtered.filter(course => 
        course.title.toLowerCase().includes(query) || 
        (course.instructor && course.instructor.name.toLowerCase().includes(query))
      );
    }

    this.filteredCourses = filtered;
  }
}
