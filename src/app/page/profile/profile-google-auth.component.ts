import { Component } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators, ReactiveFormsModule, FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { MatRippleModule } from '@angular/material/core';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AuthService } from '../../services/auth.service';
import { DataService } from '../../services/data.service';

declare var QRCode: any;

@Component({
  selector: 'app-profile-google-auth',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, FormsModule, MatRippleModule],
  templateUrl: './profile-google-auth.component.html',
  styleUrls: ['./profile-google-auth.component.scss']
})
export class ProfileGoogleAuthComponent {
  enableForm: FormGroup;
  disableForm: FormGroup;
  enableCode: FormControl;
  disableCode: FormControl;
  isLoading: boolean = false;
  twoFactorEnabled: boolean = false;
  qrCodeUri: string = '';
  secret: string = '';

  constructor(private fb: FormBuilder, private auth: AuthService, private dataService: DataService, private snackBar: MatSnackBar) {
    this.enableCode = new FormControl('', [Validators.required, Validators.minLength(6), Validators.maxLength(6)]);
    this.disableCode = new FormControl('', [Validators.required, Validators.minLength(6), Validators.maxLength(6)]);
    this.enableForm = this.fb.group({
      code: this.enableCode,
    });
    this.disableForm = this.fb.group({
      code: this.disableCode,
    });
  }

  ngOnInit(): void {
    this.loadStatus();
    this.loadQRCodeScript();
  }

  loadQRCodeScript() {
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
    script.async = true;
    document.body.appendChild(script);
  }

  loadStatus() {
    this.auth.getTwoFactorStatus().subscribe((res: any) => {
      this.twoFactorEnabled = !!res.enabled;
    });
  }

  onGenerate() {
    if (this.isLoading) return;
    this.isLoading = true;
    this.auth.generateTwoFactor().subscribe((res: any) => {
      this.qrCodeUri = res.qr_code_uri;
      this.secret = res.secret;
      this.isLoading = false;

      setTimeout(() => {
        const qrContainer = document.getElementById('qrcode');
        if (qrContainer) {
          qrContainer.innerHTML = '';
          new QRCode(qrContainer, {
            text: this.qrCodeUri,
            width: 300,
            height: 300,
            correctLevel: QRCode.CorrectLevel.H
          });
        }
      }, 100);

      this.snackBar.open('Scan the QR code with Google Authenticator, then enter the code to enable.', 'OK', { duration: 5000 });
    }, () => {
      this.isLoading = false;
    });
  }

  onEnable() {
    this.enableForm.markAllAsTouched();
    if (this.enableForm.invalid) {
      this.snackBar.open('Please enter a valid 6-digit code.', 'OK', { duration: 3000 });
      return;
    }

    if (this.isLoading) return;
    this.isLoading = true;

    this.auth.enableTwoFactor({ one_time_password: this.enableForm.value.code }).subscribe(() => {
      this.isLoading = false;
      this.twoFactorEnabled = true;
      this.qrCodeUri = '';
      this.secret = '';
      this.enableForm.reset();
      this.snackBar.open('2FA is now enabled.', 'OK', { duration: 3000 });
    }, (error: any) => {
      this.isLoading = false;
      if (error.error.message === 'Invalid 2FA code') {
        this.snackBar.open('Invalid 2FA code.', 'OK', { duration: 3000 });
        return;
      }
      this.snackBar.open('Unable to enable 2FA.', 'OK', { duration: 3000 });
    });
  }

  onDisable() {
    this.disableForm.markAllAsTouched();
    if (this.disableForm.invalid) {
      this.snackBar.open('Please enter a valid 6-digit code.', 'OK', { duration: 3000 });
      return;
    }

    if (this.isLoading) return;
    this.isLoading = true;

    this.auth.disableTwoFactor({ one_time_password: this.disableForm.value.code }).subscribe(() => {
      this.isLoading = false;
      this.twoFactorEnabled = false;
      this.disableForm.reset();
      this.snackBar.open('2FA has been disabled.', 'OK', { duration: 3000 });
    }, (error: any) => {
      this.isLoading = false;
      if (error.error.message === 'Invalid 2FA code') {
        this.snackBar.open('Invalid 2FA code.', 'OK', { duration: 3000 });
        return;
      }
      this.snackBar.open('Unable to disable 2FA.', 'OK', { duration: 3000 });
    });
  }
}