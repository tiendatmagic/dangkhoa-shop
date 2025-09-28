import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { DataService } from '../../../services/data.service';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-admin-order-detail',
  standalone: false,
  templateUrl: './admin-order-detail.component.html',
  styleUrl: './admin-order-detail.component.scss'
})
export class AdminOrderDetailComponent {
  orderData: any;
  id: any;
  deliveryFee: number = 0;
  isLoading: boolean = false;
  constructor(private route: ActivatedRoute, private router: Router, private dataService: DataService, private http: HttpClient, private auth: AuthService) {
    this.deliveryFee = this.dataService.deliveryFee;
  }

  ngOnInit() {
    this.id = this.route.snapshot.paramMap.get('id');
    if (this.id) {
      this.isLoading = true;
      this.auth.getOrderDetail({ id: this.id }).subscribe(
        (res: any) => {
          this.orderData = res;
          this.isLoading = false;
        },
        (error: any) => {
          console.error(error);
          this.isLoading = false;
        }
      )
    }
    else {
      this.router.navigate(['/order']);
    }
  }

  viewOnBSCScan(tx: string) {
    window.open(`https://bscscan.com/tx/${tx}`, '_blank');
  }

  onChangeStatus(event: any) {
    const value = event.target.value;

    if (!value) {
      return;
    }

    this.auth.updateOrderStatus({ id: this.id, status: value }).subscribe(
      (res: any) => {
        this.orderData.order.status = value;
      },
      (error: any) => {
        console.error(error);
      }
    )
  }
}