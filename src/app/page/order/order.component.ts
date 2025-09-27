import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { DataService } from '../../services/data.service';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-order',
  standalone: false,
  templateUrl: './order.component.html',
  styleUrl: './order.component.scss'
})
export class OrderComponent {
  deliveryFee: number = 0;
  orderData: any;
  isLoading: boolean = false;

  constructor(private route: ActivatedRoute, private router: Router, private dataService: DataService, private http: HttpClient, private auth: AuthService) {
    this.deliveryFee = this.dataService.deliveryFee;
  }

  ngOnInit() {
    this.isLoading = true;
    this.auth.getMyOrder().subscribe(
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

  viewOrder() {

  }
}
