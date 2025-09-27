import { Component } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { DataService } from '../../services/data.service';
import { Router } from '@angular/router';
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
  loginForm: FormGroup;
  isShow: boolean = false;
  isLoading: boolean = false;

  constructor(_fb: FormBuilder, private dataService: DataService, private router: Router, private auth: AuthService) {
    this.email = new FormControl('', [
      Validators.required, Validators.email
    ]);
    this.password = new FormControl('', [
      Validators.required
    ]);
    this.loginForm = _fb.group({
      email: this.email,
      password: this.password,
    });
  }

  ngOnInit() {
    var getToken = localStorage.getItem('dat-shop-renew');

    if (getToken) {
      this.router.navigate(['/']);
    }
  }

  onLogin() {
    this.loginForm.markAllAsTouched();
    if (this.loginForm.valid) {
      var data: any = {
        'email': this.loginForm.value.email,
        'password': this.loginForm.value.password
      };

      this.isLoading = this.auth.isLoading;
      this.loginForm.disable();


      this.auth.onLogin(data).subscribe((res: any) => {
        localStorage.setItem('dat-shop-token', res.access_token);
        localStorage.setItem('dat-shop-renew', res.refresh_token);
        this.router.navigate(['/home']);
        this.auth.getToken = res.access_token;
        this.auth.isLogin = true;
        this.isLoading = this.auth.isLoading;
        localStorage.setItem('dat-shop-profile', JSON.stringify(res['information']));
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


  }
}
