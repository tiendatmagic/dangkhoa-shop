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
    this.auth.ensureAuthenticated().subscribe((ok) => {
      if (ok) this.router.navigate(['/']);
    });
  }

  onRegister() {
    if (this.registerForm.value.password == this.registerForm.value.confirmPassword) {
      var data: any = {
        'email': this.registerForm.value.email,
        'password': this.registerForm.value.password
      };
      this.registerForm.disable();

      this.auth.onRegister(data).subscribe((res: any) => {
        this.router.navigate(['/home']);
        this.auth.isLogin = true;
        this.auth.onLoad = true;
        if (res?.information) {
          localStorage.setItem('dangkhoa-profile', JSON.stringify(res.information));
          this.auth.isAdmin = res?.information?.is_admin || 0;
        }
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
