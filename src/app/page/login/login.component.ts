import { Component } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { DataService } from '../../services/data.service';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  standalone: false,
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss'
})
export class LoginComponent {
  email: FormControl;
  password: FormControl;
  otp: FormControl;
  loginForm: FormGroup;
  isShow: boolean = false;
  isLoading: boolean = false;
  isTwoFactorStep: boolean = false;

  constructor(_fb: FormBuilder, private dataService: DataService, private router: Router, private snackBar: MatSnackBar, private auth: AuthService) {
    this.email = new FormControl('', [
      Validators.required, Validators.email
    ]);
    this.password = new FormControl('', [
      Validators.required
    ]);
    this.otp = new FormControl('', []);
    this.loginForm = _fb.group({
      email: this.email,
      password: this.password,
      otp: this.otp,
    });
  }

  ngOnInit() {
    this.auth.ensureAuthenticated().subscribe((ok) => {
      if (ok) this.router.navigate(['/']);
    });
  }

  onLogin() {
    this.loginForm.markAllAsTouched();

    if (!this.isTwoFactorStep) {
      if (this.loginForm.valid) {
        var data: any = {
          'email': this.loginForm.value.email,
          'password': this.loginForm.value.password
        };

        this.isLoading = this.auth.isLoading;
        this.loginForm.disable();

        this.auth.onLogin(data).subscribe((res: any) => {
          if (res && res.requires_2fa) {
            this.isTwoFactorStep = true;
            this.otp.setValidators([Validators.required, Validators.minLength(6), Validators.maxLength(6)]);
            this.otp.updateValueAndValidity();
            this.loginForm.enable();
            this.isLoading = false;
            this.auth.isLoading = false;
            this.snackBar.open('Enter your Google Authenticator code to continue.', 'OK', { duration: 5000 });
            return;
          }

          this.router.navigate(['/home']);
          this.auth.isLogin = true;
          this.auth.isAdmin = res?.information?.is_admin || 0;
          this.isLoading = this.auth.isLoading;
          if (res?.information) {
            localStorage.setItem('dangkhoa-profile', JSON.stringify(res.information));
          }
          this.loginForm.enable();
        },
          (error: any) => {
            this.loginForm.enable();
            if (error.error.message == 'Unauthorized') {
              this.dataService.showNotify('Login error', 'Login failed, please check your information again', 'error', true, true, false);
              this.auth.isLoading = false;
              this.isLoading = this.auth.isLoading;
            }
          }
        );
      }

      return;
    }

    if (this.otp.invalid) {
      this.dataService.showNotify('Error', 'Please enter a valid 2FA code.', 'error', true, true, false);
      return;
    }

    var verifyData: any = {
      'email': this.loginForm.value.email,
      'password': this.loginForm.value.password,
      'one_time_password': this.loginForm.value.otp
    };

    this.isLoading = this.auth.isLoading;
    this.loginForm.disable();

    this.auth.onLogin2fa(verifyData).subscribe((res: any) => {
      this.router.navigate(['/home']);
      this.auth.isLogin = true;
      this.auth.isAdmin = res?.information?.is_admin || 0;
      this.isLoading = this.auth.isLoading;
      if (res?.information) {
        localStorage.setItem('dangkhoa-profile', JSON.stringify(res.information));
      }
      this.loginForm.enable();
    },
      (error: any) => {
        this.loginForm.enable();
        if (error.error.message == 'Invalid 2FA code') {
          this.dataService.showNotify('Login error', 'Invalid 2FA code, please try again.', 'error', true, true, false);
        } else if (error.error.message == 'Unauthorized') {
          this.dataService.showNotify('Login error', 'Login failed, please check your information again', 'error', true, true, false);
        }
        this.auth.isLoading = false;
        this.isLoading = this.auth.isLoading;
      }
    );
  }
}
