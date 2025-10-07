import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { DataService } from '../../../services/data.service';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-admin-product',
  standalone: false,
  templateUrl: './admin-product.component.html',
  styleUrl: './admin-product.component.scss'
})
export class AdminProductComponent {
  productData: any;
  isLoading: boolean = false;
  per_page: number = 10;
  page: number = 1;
  totalProducts: number = 0;
  urlEnv: string = '';
  constructor(private route: ActivatedRoute, private router: Router, private dataService: DataService, private http: HttpClient, private auth: AuthService) {
  }

  ngOnInit() {
    this.urlEnv = this.auth.urlEnv.replace(/\/$/, '');
    this.isLoading = true;
    this.auth.getAllProduct({
      page: 1
    }).subscribe(
      (res: any) => {
        this.productData = res.data;
        this.totalProducts = res.total;
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
    this.auth.getAllProduct({
      per_page: this.per_page += 10,
    }).subscribe(
      (res: any) => {
        this.productData = res.data;
        this.totalProducts = res.total;
        this.isLoading = false;
      },
      (error: any) => {
        console.error(error);
        this.isLoading = false;
      }
    )
  }
}
