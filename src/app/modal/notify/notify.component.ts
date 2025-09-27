import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';

@Component({
  selector: 'app-notify',
  standalone: false,
  templateUrl: './notify.component.html',
  styleUrl: './notify.component.scss'
})
export class NotifyComponent {
  public datas: any = [];
  public status: number = 0;
  public countdown: number = 0;
  errorImg: string = '/assets/images/icon-error.png';
  successImg: string = '/assets/images/icon-success.png';

  constructor(public dialogRef: MatDialogRef<NotifyComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { }

  ngOnInit() {
    this.datas = this.data;
    if (this.data.nrStatus === 429) {
      this.status = this.data.nrStatus;
      this.countdown = 5;
      var count = setInterval(() => {
        this.countdown--;
        if (this.countdown <= 0) {
          clearInterval(count);
        }
      }, 1000);
    }
  }

  onNoClick(): void {

    if (this.countdown === 0) {
      this.dialogRef.close();
    }

  }
}

