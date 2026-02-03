import { Component, OnInit, OnDestroy } from '@angular/core';
import { Web3Service } from '../../services/web3.service';
import { DataService } from '../../services/data.service';
import { AuthService } from '../../services/auth.service';  // Import AuthService
import { ApiCacheService } from '../../services/api-cache.service';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-collection',
  standalone: false,
  templateUrl: './collection.component.html',
  styleUrl: './collection.component.scss'
})
export class CollectionComponent implements OnInit, OnDestroy {
  productList: any[] = [];
  allProducts: any[] = [];
  filterArray: string[] = [];
  selectedPrice: number = 0;
  selectedSort: string = 'relevant';
  searchKeyword: string = '';
  currentPage: number = 1;
  totalPages: number = 0;
  hasMorePages: boolean = true;
  isLoading: boolean = false;
  isLoadingMore: boolean = false;
  private productsSub: any = null;

  constructor(
    private web3Service: Web3Service,
    private dataService: DataService,
    private auth: AuthService,
    private route: ActivatedRoute,
    private apiCache: ApiCacheService
  ) { }

  ngOnDestroy(): void {
    if (this.productsSub) {
      try { this.productsSub.unsubscribe(); } catch { }
      this.productsSub = null;
    }
  }

  ngOnInit() {
    // read query params (e.g., ?category=men)
    // and initialize filters accordingly
    // then load products
    this.route.queryParams.subscribe((params: any) => {
      if (params && params.category) {
        const cats = String(params.category).split(',').map((c: string) => c.trim()).filter(Boolean);
        this.filterArray = cats;
      } else {
        this.filterArray = [];
      }

      if (params && params.q) {
        this.searchKeyword = String(params.q).trim();
      } else {
        this.searchKeyword = '';
      }
      this.loadProducts(true);
    });
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

    if (this.searchKeyword) {
      params.q = this.searchKeyword;
    }


    // cancel previous pending products request
    if (this.productsSub) {
      try { this.productsSub.unsubscribe(); } catch { }
      this.productsSub = null;
    }
    const key = 'products_' + JSON.stringify(params || {});
    this.productsSub = this.apiCache.getCached(key, this.auth.getProducts(params)).subscribe((res: any) => {
      const newProducts = res.data || res;
      this.productList.push(...newProducts);
      this.totalPages = res.last_page || Math.ceil((res.total || 0) / 12);
      this.hasMorePages = this.currentPage < this.totalPages;
      this.isLoading = false;
      this.isLoadingMore = false;
    }, (error: any) => {
      this.isLoading = false;
      this.isLoadingMore = false;
    });
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

  // Returns a cached random sales count for a given product id (1..15).
  // Cached in sessionStorage per product id for 5 minutes.
  getSales(productId: any): number {
    try {
      const key = 'sales_' + String(productId);
      const raw = sessionStorage.getItem(key);
      const now = Date.now();
      const ttl = 5 * 60 * 1000; // 5 minutes
      if (raw) {
        const obj = JSON.parse(raw);
        if (obj && typeof obj.value === 'number' && typeof obj.ts === 'number') {
          if (now - obj.ts < ttl) {
            return obj.value;
          } else {
            try {
              for (let i = sessionStorage.length - 1; i >= 0; i--) {
                const k = sessionStorage.key(i);
                if (k && k.indexOf('sales_') === 0) {
                  sessionStorage.removeItem(k);
                }
              }
            } catch (err) {
              // ignore and continue to generate new value
            }
          }
        }
      }
      const value = Math.floor(Math.random() * 15) + 1; // 1..15
      sessionStorage.setItem(key, JSON.stringify({ value, ts: now }));
      return value;
    } catch (e) {
      return Math.floor(Math.random() * 15) + 1;
    }
  }

  test() {
    this.web3Service.getBalanceFunc('0x18E215E111aa8877266E9F8CDeDf21f605777777');
  }
}