import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { DataService } from '../../../services/data.service';
import { AuthService } from '../../../services/auth.service';
@Component({
  selector: 'app-admin-order',
  standalone: false,
  templateUrl: './admin-order.component.html',
  styleUrl: './admin-order.component.scss'
})
export class AdminOrderComponent {
  deliveryFee: number = 0;
  orderData: any;
  isLoading: boolean = false;
  per_page: number = 10;
  page: number = 1;
  totalOrders: number = 0;
  constructor(private route: ActivatedRoute, private router: Router, private dataService: DataService, private http: HttpClient, private auth: AuthService) {
    this.deliveryFee = this.dataService.deliveryFee;
  }

  ngOnInit() {
    this.isLoading = true;
    this.auth.getAllOrder({
      page: 1
    }).subscribe(
      (res: any) => {
        this.orderData = res.data;
        this.totalOrders = res.total;
        this.isLoading = false;
      },
      (error: any) => {
        console.error(error);
        this.isLoading = false;
      }
    )
  }

  viewMore() {

    if (this.isLoading) return;

    this.isLoading = true;
    this.auth.getAllOrder({
      per_page: this.per_page += 10,
    }).subscribe(
      (res: any) => {
        this.orderData = res.data;
        this.totalOrders = res.total;
        this.isLoading = false;
      },
      (error: any) => {
        console.error(error);
        this.isLoading = false;
      }
    )
  }
}