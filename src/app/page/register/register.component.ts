import { Component } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Title } from '@angular/platform-browser';
import { debounceTime, distinctUntilChanged } from 'rxjs';
import { DataService } from '../../services/data.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-register',
  standalone: false,
  templateUrl: './register.component.html',
  styleUrl: './register.component.scss'
})
export class RegisterComponent {
  email: FormControl;
  password: FormControl;
  confirmPassword: FormControl;
  registerForm: FormGroup;
  emailExists: boolean = false;
  isShow: boolean = false;
  isShowConfirm: boolean = false;
  constructor(_fb: FormBuilder, private router: Router, private route: ActivatedRoute, private dataService: DataService, private auth: AuthService) {
    this.email = new FormControl('', [
      Validators.required, Validators.email
    ]);
    this.password = new FormControl('', [
      Validators.required
    ]);
    this.confirmPassword = new FormControl('', [
      Validators.required
    ]);
    this.registerForm = _fb.group({
      email: this.email,
      password: this.password,
      confirmPassword: this.confirmPassword
    });
  }

  ngOnInit() {
    var getToken = localStorage.getItem('dat-shop-renew');
    if (getToken) {
      this.router.navigate(['/']);
    }
  }

  onRegister() {
    if (this.registerForm.value.password == this.registerForm.value.confirmPassword) {
      var data: any = {
        'email': this.registerForm.value.email,
        'password': this.registerForm.value.password
      };
      this.registerForm.disable();

      this.auth.onRegister(data).subscribe((res: any) => {
        localStorage.setItem('dat-shop-token', res.access_token);
        localStorage.setItem('dat-shop-renew', res.refresh_token);
        this.auth.getToken = res.access_token;
        this.router.navigate(['/home']);
        this.auth.isLogin = true;
        this.auth.onLoad = true;
        this.registerForm.enable();
      },

        (error: any) => {
          this.auth.isLogin = false;
          this.registerForm.enable();
          this.dataService.showNotify('Registration error', 'Registration failed, please check your information again', 'error', true, true, false);
        }
      );


    }
  }
}
