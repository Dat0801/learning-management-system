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
  activeTab: 'profile' | 'password' = 'profile';
  
  isUpdatingProfile = false;
  isChangingPassword = false;
  message = { type: '', content: '' };

  constructor(
    private authService: AuthService,
    private fb: FormBuilder
  ) {
    this.profileForm = this.fb.group({
      name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]]
    });

    this.passwordForm = this.fb.group({
      current_password: ['', Validators.required],
      new_password: ['', [Validators.required, Validators.minLength(8)]],
      new_password_confirmation: ['', Validators.required]
    }, { validator: this.passwordMatchValidator });
  }

  ngOnInit() {
    this.user = this.authService.getUser();
    if (this.user) {
      this.profileForm.patchValue({
        name: this.user.name,
        email: this.user.email
      });
    }
  }

  passwordMatchValidator(g: FormGroup) {
    return g.get('new_password')?.value === g.get('new_password_confirmation')?.value
      ? null : { mismatch: true };
  }

  switchTab(tab: 'profile' | 'password') {
    this.activeTab = tab;
    this.message = { type: '', content: '' };
  }

  onSubmitProfile() {
    if (this.profileForm.invalid) return;

    this.isUpdatingProfile = true;
    this.message = { type: '', content: '' };

    this.authService.updateProfile(this.profileForm.value).subscribe({
      next: (res) => {
        this.isUpdatingProfile = false;
        this.message = { type: 'success', content: 'Profile updated successfully!' };
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

    this.authService.changePassword({
      current_password: this.passwordForm.get('current_password')?.value,
      new_password: this.passwordForm.get('new_password')?.value,
      new_password_confirmation: this.passwordForm.get('new_password_confirmation')?.value
    }).subscribe({
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
