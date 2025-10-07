import { Component, OnInit } from '@angular/core';
import { Web3Service } from '../../services/web3.service';
import { DataService } from '../../services/data.service';
import { AuthService } from '../../services/auth.service';  // Import AuthService

@Component({
  selector: 'app-collection',
  standalone: false,
  templateUrl: './collection.component.html',
  styleUrl: './collection.component.scss'
})
export class CollectionComponent implements OnInit {
  productList: any[] = [];
  allProducts: any[] = [];
  filterArray: string[] = [];
  selectedPrice: number = 0;
  selectedSort: string = 'relevant';
  currentPage: number = 1;
  totalPages: number = 0;
  hasMorePages: boolean = true;
  isLoading: boolean = false;
  isLoadingMore: boolean = false;

  constructor(
    private web3Service: Web3Service,
    private dataService: DataService,
    private auth: AuthService
  ) { }

  ngOnInit() {
    this.loadProducts();
  }

  loadProducts(reset: boolean = false) {
    if (reset) {
      this.productList = [];
      this.currentPage = 1;
      this.hasMorePages = true;
    }

    this.isLoading = this.currentPage === 1;

    const params: any = {
      page: this.currentPage.toString(),
      per_page: '12',
      sort: this.selectedSort
    };

    if (this.filterArray.length > 0) {
      params.category = this.filterArray.join(',');
    }

    if (this.selectedPrice > 0) {
      params.min_price = this.selectedPrice.toString();
    }


    this.auth.getProducts(params).subscribe(
      (res: any) => {
        const newProducts = res.data || res;
        this.productList.push(...newProducts);
        this.totalPages = res.last_page || Math.ceil((res.total || 0) / 12);
        this.hasMorePages = this.currentPage < this.totalPages;
        this.isLoading = false;
        this.isLoadingMore = false;
      },
      (error: any) => {
        this.isLoading = false;
        this.isLoadingMore = false;
      }
    );
  }

  loadMoreProducts() {
    if (this.hasMorePages && !this.isLoadingMore) {
      this.currentPage++;
      this.isLoadingMore = true;
      this.loadProducts(false);
    }
  }

  onCategoryChange(category: string, event: any) {
    if (event.target.checked) {
      if (!this.filterArray.includes(category)) {
        this.filterArray.push(category);
      }
    } else {
      this.filterArray = this.filterArray.filter(c => c !== category);
    }
    this.resetAndLoad();
  }

  onPriceChange(price: number) {
    this.selectedPrice = price;
    this.resetAndLoad();
  }

  onSortChange(event: any) {
    this.selectedSort = event.target.value;
    this.resetAndLoad();
  }

  private resetAndLoad() {
    this.currentPage = 1;
    this.productList = [];
    this.loadProducts(true);
  }

  getImageUrl(imagePath: string | string[]): string {
    let path: any = '';
    if (Array.isArray(imagePath) && imagePath.length > 0) {
      path = imagePath[0];
    } else if (imagePath) {
      path = imagePath;
    }
    const baseUrl = this.auth.getBaseUrl();
    return path ? baseUrl + path : 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg';
  }

  get bestSellerProducts() {
    return this.productList.filter(product => product.is_best_seller == 1);
  }

  handleImageError(event: any) {
    event.target.src = 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg';
  }

  test() {
    this.web3Service.getBalanceFunc('0x18E215E111aa8877266E9F8CDeDf21f605777777');
  }
}