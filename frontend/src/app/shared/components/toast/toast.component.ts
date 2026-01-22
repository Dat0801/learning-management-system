import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ToastService, Toast } from '../../../services/toast.service';

@Component({
  selector: 'app-toast',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="toast-container">
      <div *ngFor="let toast of toastService.toasts$ | async" 
           class="toast" 
           [ngClass]="toast.type"
           (click)="toastService.remove(toast.id)">
        <span class="message">{{ toast.message }}</span>
        <span class="close">&times;</span>
      </div>
    </div>
  `,
  styles: [`
    .toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 10px;
      pointer-events: none; /* Allow clicks to pass through container */
    }

    .toast {
      padding: 12px 20px;
      border-radius: 8px;
      color: white;
      min-width: 250px;
      max-width: 400px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      animation: slideIn 0.3s ease-out;
      pointer-events: auto; /* Enable clicks on toasts */
      font-size: 0.95rem;

      &.success { background-color: #10b981; }
      &.error { background-color: #ef4444; }
      &.info { background-color: #3b82f6; }
      &.warning { background-color: #f59e0b; }

      .close {
        margin-left: 15px;
        font-size: 1.2rem;
        opacity: 0.7;
        &:hover { opacity: 1; }
      }
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
  `]
})
export class ToastComponent {
  constructor(public toastService: ToastService) {}
}
