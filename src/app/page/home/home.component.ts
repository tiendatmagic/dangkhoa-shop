import { Component, OnInit, OnDestroy } from '@angular/core';
import { Web3Service } from '../../services/web3.service';
import { DataService } from '../../services/data.service';
import { AuthService } from '../../services/auth.service';
import { Subscription } from 'rxjs';
import { ApiCacheService } from '../../services/api-cache.service';
import { CategoryService } from '../../services/category.service';

@Component({
  selector: 'app-home',
  standalone: false,
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss'
})
export class HomeComponent implements OnInit, OnDestroy {
  productList: any[] = [];
  bestSellerProducts: any[] = [];
  isLoading: boolean = false;
  isIntervalActive: any;
  // default empty — admin-provided slides will populate this via API
  slides: string[] = [];
  banner: string = '';
  currentSlide: number = 0;
  sliderInterval: any = null;
  autoplayDelay: number = 4000;
  homeCollections: any[] = [];
  private homeProductsSub: Subscription | null = null;
  private customizationSub: Subscription | null = null;
  private cacheInvalidSub: Subscription | null = null;

  constructor(
    private web3Service: Web3Service,
    private dataService: DataService,
    private auth: AuthService,
    private apiCache: ApiCacheService,
    public categoryService: CategoryService
  ) { }

  ngOnInit() {
    this.isLoading = true;
    this.loadHomeProducts();
    this.callLoadHomeProducts();
    this.startAutoplay();
    this.loadCustomization();
    // listen for cache invalidation events and reload customization when updated
    this.cacheInvalidSub = this.apiCache.cacheInvalidated$.subscribe((key: string | null) => {
      if (!key || key === 'public_customization' || key === 'customization') {
        // Clear any existing customization subscription then reload
        try { if (this.customizationSub) { this.customizationSub.unsubscribe(); this.customizationSub = null; } } catch { }
        this.loadCustomization();
      }
    });
  }

  prevSlide() {
    if (!this.slides || this.slides.length === 0) return;
    this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
    this.restartAutoplay();
  }

  nextSlide() {
    if (!this.slides || this.slides.length === 0) return;
    this.currentSlide = (this.currentSlide + 1) % this.slides.length;
    this.restartAutoplay();
  }

  goToSlide(index: number) {
    if (!this.slides || this.slides.length === 0) return;
    if (index >= 0 && index < this.slides.length) {
      this.currentSlide = index;
      this.restartAutoplay();
    }
  }

  callLoadHomeProducts() {
    clearTimeout(this.isIntervalActive);
    this.isIntervalActive = setTimeout(() => {
      this.loadHomeProducts();
    }, 30000)
  }

  ngOnDestroy() {
    clearTimeout(this.isIntervalActive);
    this.stopAutoplay();
    if (this.homeProductsSub) {
      this.homeProductsSub.unsubscribe();
      this.homeProductsSub = null;
    }
    if (this.customizationSub) {
      this.customizationSub.unsubscribe();
      this.customizationSub = null;
    }
    if (this.cacheInvalidSub) {
      this.cacheInvalidSub.unsubscribe();
      this.cacheInvalidSub = null;
    }
  }

  loadHomeProducts() {
    clearTimeout(this.isIntervalActive);
    // Cancel previous pending home products request, keep only the latest
    if (this.homeProductsSub) {
      try { this.homeProductsSub.unsubscribe(); } catch { }
      this.homeProductsSub = null;
    }
    this.isLoading = true;
    // use cache for home products to avoid repeated calls within TTL
    this.homeProductsSub = this.apiCache.getCached('home_products', this.auth.getHomeProducts()).subscribe((res: any) => {
      this.productList = res.latest_collection || [];
      this.bestSellerProducts = res.best_sellers || [];
      this.callLoadHomeProducts();
      this.isLoading = false;
    }, (error: any) => {
      console.error('Lỗi load products:', error);
      this.isLoading = false;
    });
  }

  loadCustomization() {
    // Cancel previous pending customization request
    if (this.customizationSub) {
      try { this.customizationSub.unsubscribe(); } catch { }
      this.customizationSub = null;
    }
    // cache customization for short TTL to reduce repeated calls
    this.customizationSub = this.apiCache.getCached('public_customization', this.auth.getPublicCustomization()).subscribe((res: any) => {
      console.log('public customization response:', res);
      if (res && Array.isArray(res.slides)) {
        this.slides = res.slides.map((p: string) => p && p.startsWith('http') ? p : (p ? this.auth.getBaseUrl() + p : p)).filter(Boolean);
      }
      this.homeCollections = Array.isArray(res.collections) ? res.collections : (res.collections || []);
      // load optional home banner if provided
      if (res && res.banner) {
        this.banner = res.banner && res.banner.startsWith('http') ? res.banner : (this.auth.getBaseUrl() + res.banner);
      } else if (res && res.homeBanner) {
        this.banner = res.homeBanner && res.homeBanner.startsWith('http') ? res.homeBanner : (this.auth.getBaseUrl() + res.homeBanner);
      } else {
        this.banner = '';
      }
    }, (err: any) => {
      console.error('Failed to load public customization:', err);
    });
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

  handleImageError(event: any) {
    event.target.src = 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg';
  }

  test() {
    this.web3Service.getBalanceFunc('0x18E215E111aa8877266E9F8CDeDf21f605777777');
  }

  startAutoplay() {
    this.stopAutoplay();
    if (!this.slides || this.slides.length <= 1) return;
    this.sliderInterval = setInterval(() => {
      this.nextSlide();
    }, this.autoplayDelay);
  }

  stopAutoplay() {
    if (this.sliderInterval) {
      clearInterval(this.sliderInterval);
      this.sliderInterval = null;
    }
  }

  restartAutoplay() {
    this.stopAutoplay();
    this.startAutoplay();
  }

  // Returns a cached random sales count for a given product id (1..15).
  // Cached in localStorage per product id for 5 minutes.
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
            // expired: clear all sales_* keys as requested
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
}