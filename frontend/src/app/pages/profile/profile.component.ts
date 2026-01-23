import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.scss']
})
export class ProfileComponent implements OnInit {
  user: any = null;
  profileForm: FormGroup;
  passwordForm: FormGroup;
  activeTab: 'personal-info' | 'security' | 'notifications' | 'billing' = 'personal-info';
  
  isUpdatingProfile = false;
  isChangingPassword = false;
  message = { type: '', content: '' };

  // Mock data for UI
  skills = [
    { name: 'Python Pro', icon: 'fas fa-terminal', color: '#e6f0ff', iconColor: '#3b82f6' },
    { name: 'Critical Thinker', icon: 'fas fa-lightbulb', color: '#fff7ed', iconColor: '#f97316' },
    { name: 'Data Guru', icon: 'fas fa-code', color: '#ecfdf5', iconColor: '#10b981' },
    { name: 'UI Designer', icon: 'fas fa-pen-nib', color: '#fdf4ff', iconColor: '#d946ef' }
  ];

  activityData: boolean[] = [];

  constructor(
    private authService: AuthService,
    private fb: FormBuilder
  ) {
    this.profileForm = this.fb.group({
      firstName: ['', Validators.required],
      lastName: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      phone: [''],
      bio: [''],
      linkedin: [''],
      portfolio: ['']
    });

    this.passwordForm = this.fb.group({
      current_password: ['', Validators.required],
      new_password: ['', [Validators.required, Validators.minLength(8)]],
      new_password_confirmation: ['', Validators.required]
    }, { validator: this.passwordMatchValidator });
    
    // Generate mock activity data
    this.generateActivityData();
  }

  ngOnInit() {
    this.user = this.authService.getUser();
    if (this.user) {
      const names = (this.user.name || '').split(' ');
      const firstName = names[0] || '';
      const lastName = names.slice(1).join(' ') || '';
      
      this.profileForm.patchValue({
        firstName: firstName,
        lastName: lastName,
        email: this.user.email,
        bio: 'Computer Science student passionate about full-stack development and artificial intelligence. Currently mastering React and Python.',
        phone: '+1 (555) 000-1234',
        linkedin: 'linkedin.com/in/' + firstName.toLowerCase() + lastName.toLowerCase(),
        portfolio: firstName.toLowerCase() + lastName.toLowerCase() + '.dev'
      });
    }
  }

  generateActivityData() {
    // Generate 364 days of random activity
    for (let i = 0; i < 364; i++) {
      this.activityData.push(Math.random() > 0.5);
    }
  }

  passwordMatchValidator(g: FormGroup) {
    return g.get('new_password')?.value === g.get('new_password_confirmation')?.value
      ? null : { mismatch: true };
  }

  switchTab(tab: 'personal-info' | 'security' | 'notifications' | 'billing') {
    this.activeTab = tab;
    this.message = { type: '', content: '' };
  }

  onSubmitProfile() {
    if (this.profileForm.invalid) return;

    this.isUpdatingProfile = true;
    this.message = { type: '', content: '' };

    // Combine first and last name for backend
    const formData = {
      ...this.profileForm.value,
      name: `${this.profileForm.value.firstName} ${this.profileForm.value.lastName}`.trim()
    };

    this.authService.updateProfile(formData).subscribe({
      next: (res) => {
        this.isUpdatingProfile = false;
        this.message = { type: 'success', content: 'Profile preferences saved successfully!' };
        this.user = res.data.user; // Update local user display
      },
      error: (err) => {
        this.isUpdatingProfile = false;
        this.message = { type: 'error', content: err.error?.message || 'Failed to update profile' };
      }
    });
  }

  onSubmitPassword() {
    if (this.passwordForm.invalid) return;

    this.isChangingPassword = true;
    this.message = { type: '', content: '' };

    this.authService.changePassword(this.passwordForm.value).subscribe({
      next: () => {
        this.isChangingPassword = false;
        this.message = { type: 'success', content: 'Password changed successfully!' };
        this.passwordForm.reset();
      },
      error: (err) => {
        this.isChangingPassword = false;
        this.message = { type: 'error', content: err.error?.message || 'Failed to change password' };
      }
    });
  }
}
