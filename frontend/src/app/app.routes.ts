import { Routes } from '@angular/router';
import { StudentLayoutComponent } from './layouts/student-layout/student-layout.component';
import { AdminLayoutComponent } from './layouts/admin-layout/admin-layout.component';
import { authGuard } from './core/guards/auth.guard';
import { AdminGuard } from './core/guards/admin.guard';

export const routes: Routes = [
  { path: '', redirectTo: 'home', pathMatch: 'full' },
  {
    path: '',
    component: StudentLayoutComponent,
    children: [
      {
        path: 'auth',
        loadChildren: () => import('./auth/auth.module').then(m => m.AuthModule)
      },
      {
        path: 'home',
        loadComponent: () => import('./pages/student-home/student-home.component').then(m => m.StudentHomeComponent)
      },
      {
        path: 'browse',
        loadComponent: () => import('./pages/browse-categories/browse-categories.component').then(m => m.BrowseCategoriesComponent)
      },
      {
        path: 'courses',
        loadChildren: () => import('./pages/courses/courses.module').then(m => m.CoursesModule)
      },
      { 
        path: 'my-learning', 
        loadComponent: () => import('./pages/my-learning/my-learning.component').then(m => m.MyLearningComponent),
        canActivate: [authGuard]
      },
      { 
        path: 'profile', 
        loadComponent: () => import('./pages/profile/profile.component').then(m => m.ProfileComponent),
        canActivate: [authGuard]
      },
      {
        path: 'learning/:courseId',
        loadComponent: () => import('./pages/learning/learning.component').then(m => m.LearningComponent),
        canActivate: [authGuard]
      }
    ]
  },
  {
    path: 'admin',
    component: AdminLayoutComponent,
    canActivate: [AdminGuard],
    children: [
      {
        path: 'dashboard',
        loadComponent: () => import('./pages/admin/admin-dashboard/admin-dashboard.component').then(m => m.AdminDashboardComponent)
      },
      {
        path: 'users',
        loadComponent: () => import('./pages/admin/user-management/user-management.component').then(m => m.UserManagementComponent)
      },
      {
        path: 'courses',
        loadComponent: () => import('./pages/admin/course-management/course-management.component').then(m => m.CourseManagementComponent)
      },
      {
        path: 'enrollments',
        loadComponent: () => import('./pages/admin/enrollment-management/enrollment-management.component').then(m => m.EnrollmentManagementComponent)
      },
      {
        path: 'categories',
        loadComponent: () => import('./pages/admin/category-management/category-management.component').then(m => m.CategoryManagementComponent)
      },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  }
];
