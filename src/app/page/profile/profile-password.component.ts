import { Component } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators, ReactiveFormsModule, FormsModule } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { DataService } from '../../services/data.service';
import { CommonModule } from '@angular/common';
import { MatRippleModule } from '@angular/material/core';

@Component({
  selector: 'app-profile-password',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, FormsModule, MatRippleModule],
  templateUrl: './profile-password.component.html',
  styleUrls: ['./profile-password.component.scss']
})
export class ProfilePasswordComponent {
  passwordForm: FormGroup;
  currentPassword: FormControl;
  newPassword: FormControl;
  confirmNewPassword: FormControl;
  isDisabled: boolean = false;
  showCurrent: boolean = false;
  showNew: boolean = false;
  showConfirm: boolean = false;

  constructor(private fb: FormBuilder, private auth: AuthService, private dataService: DataService) {
    this.currentPassword = new FormControl('', [Validators.required]);
    this.newPassword = new FormControl('', [Validators.required, Validators.minLength(8)]);
    this.confirmNewPassword = new FormControl('', [Validators.required, Validators.minLength(8)]);
    this.passwordForm = this.fb.group({
      currentPassword: this.currentPassword,
      newPassword: this.newPassword,
      confirmNewPassword: this.confirmNewPassword
    });
  }

  onSubmit() {
    if (this.passwordForm.invalid) {
      this.dataService.showNotify('Error', 'Please fill all password fields correctly.', 'error', true, true, false);
      return;
    }

    const current = this.passwordForm.value.currentPassword;
    const password = this.passwordForm.value.newPassword;
    const confirm = this.passwordForm.value.confirmNewPassword;

    if (password !== confirm) {
      this.dataService.showNotify('Error', 'New password and confirmation do not match.', 'error', true, true, false);
      return;
    }

    if (this.isDisabled) return;
    this.isDisabled = true;

    const payload = { password: current, newPassword: password };
    this.auth.changePassword(payload).subscribe((res: any) => {
      this.dataService.showNotify('Success', 'Password changed successfully.', 'success', true, true, false);
      this.passwordForm.reset();
      this.isDisabled = false;
    }, (err: any) => {
      this.dataService.showNotify('Error', 'Unable to change password.', 'error', true, true, false);
      this.isDisabled = false;
    });
  }
}
