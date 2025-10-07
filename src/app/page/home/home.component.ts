import { Component, OnInit } from '@angular/core';
import { Web3Service } from '../../services/web3.service';
import { DataService } from '../../services/data.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-home',
  standalone: false,
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss'
})
export class HomeComponent implements OnInit {
  productList: any[] = [];
  bestSellerProducts: any[] = [];
  isLoading: boolean = false;

  constructor(
    private web3Service: Web3Service,
    private dataService: DataService,
    private auth: AuthService
  ) { }

  ngOnInit() {
    this.loadHomeProducts();
  }

  loadHomeProducts() {
    this.isLoading = true;
    this.auth.getHomeProducts().subscribe(
      (res: any) => {
        this.productList = res.latest_collection || [];
        this.bestSellerProducts = res.best_sellers || [];
        console.log('Loaded products:', this.productList);
        this.isLoading = false;
      },
      (error: any) => {
        console.error('Lỗi load products:', error);
        this.isLoading = false;
      }
    );
  }

  getImageUrl(imagePath: string | string[]): string {
    let path: any = '';
    if (Array.isArray(imagePath) && imagePath.length > 0) {
      path = imagePath[0];
    } else if (imagePath) {
      path = imagePath;
    }
    const baseUrl = this.auth.getBaseUrl();
    return path ? (baseUrl + path) : 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg';
  }

  get bestSellerProductsFiltered() {
    return this.productList.filter(product => product.is_best_seller == 1);
  }

  test() {
    this.web3Service.getBalanceFunc('0x18E215E111aa8877266E9F8CDeDf21f605777777');
  }
}