import { Component } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MatDialog } from '@angular/material/dialog';
import { AuthService } from '../../services/auth.service';
import { DataService } from '../../services/data.service';



@Component({
  selector: 'app-profile',
  standalone: false,
  templateUrl: './profile.component.html',
  styleUrl: './profile.component.scss'
})
export class ProfileComponent {
  fullName: FormControl;
  phone: FormControl;
  address: FormControl;
  email: FormControl;
  profileForm: FormGroup;
  imgError: string = "";
  data: any;
  username: string = "";
  avatarImg: string = 'assets/images/avatar/avatar_1.jpg';
  isDisabled: boolean = false;
  public profile: any = [];
  constructor(_fb: FormBuilder, private auth: AuthService, public dialog: MatDialog, private dataService: DataService) {
    this.fullName = new FormControl('', [
      Validators.required
    ]);
    this.phone = new FormControl('', [
    ]);
    this.address = new FormControl('', [
    ]);
    this.email = new FormControl('', [
      Validators.required
    ]);
    this.profileForm = _fb.group({
      fullName: this.fullName,
      phone: this.phone,
      address: this.address,
      email: this.email,
    });
  }
  ngOnInit(): void {
    this.auth.isGetMe = false;
    this.data = (localStorage.getItem('dat-shop-profile'));
    this.data = JSON.parse(this.data);

    if (!this.data) {
      this.auth.onMe({}).subscribe((res: any) => {
        this.profileForm.controls['fullName'].setValue(res.full_name);
        this.profileForm.controls['phone'].setValue(res.phone);
        this.profileForm.controls['address'].setValue(res.address);
        this.profileForm.controls['email'].setValue(res.email);
      },
        (error: any) => {
          if (error.status == 0 && error.statusText == 'Unknown Error') {
            localStorage.removeItem("dat-shop-renew");
            localStorage.removeItem("dat-shop-token");
          }
        }
      );
    }
    else {
      this.profileForm.controls['fullName'].setValue(this.data.full_name);
      this.profileForm.controls['phone'].setValue(this.data.phone);
      this.profileForm.controls['address'].setValue(this.data.address);
      this.profileForm.controls['email'].setValue(this.data.email);
      this.profile = (localStorage.getItem('dat-shop-profile'));
      this.profile = JSON.parse(this.profile);
    }
  }

  onSubmitForm() {
    var data = {
      full_name: this.profileForm.value.fullName,
      phone: this.profileForm.value.phone,
      email: this.profileForm.value.email,
      address: this.profileForm.value.address
    }
    if (this.profileForm.invalid) {
      this.dataService.showNotify('Error', 'The information you entered is incorrect or missing.', 'error', true, true, false);
      return;
    }

    if (this.isDisabled) {
      return;
    }

    this.isDisabled = true;
    this.auth.profile(data).subscribe((res: any) => {
      if (res && res.success == 'profile_updated') {
        this.dataService.showNotify('Success', 'Update information successfully', 'success', true, true, false);
        localStorage.setItem('dat-shop-profile', JSON.stringify(res.data));
        this.isDisabled = false;
      }
      else {

        this.dataService.showNotify('Error saving information', 'The information you entered is incorrect or missing.', 'error', true, true, false);
        this.isDisabled = false;

      }
      this.isDisabled = false;
    },

      (error: any) => {
        this.isDisabled = false;
      }
    );
  }

  ngDoCheck() {
    this.profile = (localStorage.getItem('dat-shop-profile'));
    this.profile = JSON.parse(this.profile);
  }

  onImgError(event: any) {
    event.target.src = this.auth.imgError;
  }

}