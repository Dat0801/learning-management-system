import { Routes } from '@angular/router';
import { StudentLayoutComponent } from './layouts/student-layout/student-layout.component';
import { authGuard } from './core/guards/auth.guard';

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
  }
];
