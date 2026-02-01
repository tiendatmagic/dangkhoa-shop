import { Component } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { DataService } from '../../services/data.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-reset-password',
  standalone: false,
  templateUrl: './reset-password.component.html',
  styleUrl: './reset-password.component.scss'
})
export class ResetPasswordComponent {
  email: FormControl;
  code: FormControl;
  password: FormControl;
  confirmPassword: FormControl;
  resetForm: FormGroup;
  isLoading: boolean = false;
  isShow: boolean = false;
  isShowConfirm: boolean = false;
  emailLocked: boolean = false;

  constructor(
    _fb: FormBuilder,
    private router: Router,
    private route: ActivatedRoute,
    private dataService: DataService,
    private auth: AuthService
  ) {
    this.email = new FormControl('', [Validators.required, Validators.email]);
    this.code = new FormControl('', [
      Validators.required,
      Validators.minLength(6),
      Validators.maxLength(6),
      Validators.pattern(/^[0-9]{6}$/)
    ]);
    this.password = new FormControl('', [Validators.required, Validators.minLength(8)]);
    this.confirmPassword = new FormControl('', [Validators.required, Validators.minLength(8)]);

    this.resetForm = _fb.group({
      email: this.email,
      code: this.code,
      password: this.password,
      confirmPassword: this.confirmPassword
    });
  }

  ngOnInit() {
    const emailParam = this.route.snapshot.queryParamMap.get('email');
    if (emailParam) {
      this.email.setValue(emailParam);
      this.emailLocked = true;
    }
  }

  onSubmit() {
    this.resetForm.markAllAsTouched();

    if (this.resetForm.invalid) {
      this.dataService.showNotify('Error', 'Please fill in all fields correctly.', 'error', true, true, false);
      return;
    }

    if (this.password.value !== this.confirmPassword.value) {
      this.dataService.showNotify('Error', 'Passwords do not match.', 'error', true, true, false);
      return;
    }

    const data: any = {
      email: this.resetForm.value.email,
      code: this.resetForm.value.code,
      password: this.resetForm.value.password
    };

    this.isLoading = true;
    this.resetForm.disable();

    this.auth.resetPassword(data).subscribe({
      next: () => {
        this.dataService.showNotify('Success', 'Password reset successfully. Please login again.', 'success', true, true, false);
        this.isLoading = false;
        this.resetForm.enable();
        this.router.navigate(['/login']);
      },
      error: () => {
        this.isLoading = false;
        this.resetForm.enable();
        this.dataService.showNotify('Error', 'Invalid code or reset failed.', 'error', true, true, false);
      }
    });
  }
}
