import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { DataService } from '../../services/data.service';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-order-detail',
  standalone: false,
  templateUrl: './order-detail.component.html',
  styleUrl: './order-detail.component.scss'
})
export class OrderDetailComponent {
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
}