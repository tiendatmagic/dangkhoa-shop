import { Component } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { DataService } from '../../services/data.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-forgot-password',
  standalone: false,
  templateUrl: './forgot-password.component.html',
  styleUrl: './forgot-password.component.scss'
})
export class ForgotPasswordComponent {
  email: FormControl;
  forgotForm: FormGroup;
  isLoading: boolean = false;

  constructor(
    _fb: FormBuilder,
    private router: Router,
    private dataService: DataService,
    private auth: AuthService
  ) {
    this.email = new FormControl('', [Validators.required, Validators.email]);
    this.forgotForm = _fb.group({
      email: this.email
    });
  }

  onSubmit() {
    this.forgotForm.markAllAsTouched();

    if (this.forgotForm.invalid) {
      this.dataService.showNotify('Error', 'Please enter a valid email.', 'error', true, true, false);
      return;
    }

    const data: any = {
      email: this.forgotForm.value.email
    };

    this.isLoading = true;
    this.forgotForm.disable();

    this.auth.forgotPassword(data).subscribe({
      next: () => {
        this.dataService.showNotify('Success', 'Verification code sent to your email.', 'success', true, true, false);
        this.isLoading = false;
        this.forgotForm.enable();
        this.router.navigate(['/reset-password'], { queryParams: { email: data.email } });
      },
      error: () => {
        this.isLoading = false;
        this.forgotForm.enable();
        this.dataService.showNotify('Error', 'Email not found or request failed.', 'error', true, true, false);
      }
    });
  }
}
