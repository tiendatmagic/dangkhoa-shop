import { Component, OnInit, OnDestroy } from '@angular/core';
import { Web3Service } from '../../services/web3.service';
import { DataService } from '../../services/data.service';
import { AuthService } from '../../services/auth.service';
import { Subscription } from 'rxjs';

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
  slides: string[] = [
    'https://picsum.photos/id/1018/1200/600',
    'https://picsum.photos/id/1025/1200/600',
    'https://picsum.photos/id/1035/1200/600',
    'https://picsum.photos/id/1043/1200/600',
    'https://picsum.photos/id/1050/1200/600',
    'https://picsum.photos/id/1062/1200/600'
  ];
  currentSlide: number = 0;
  sliderInterval: any = null;
  autoplayDelay: number = 4000;
  homeCollections: any[] = [];

  constructor(
    private web3Service: Web3Service,
    private dataService: DataService,
    private auth: AuthService
  ) { }

  ngOnInit() {
    this.isLoading = true;
    this.loadHomeProducts();
    this.callLoadHomeProducts();
    this.startAutoplay();
    this.loadCustomization();
  }

  prevSlide() {
    this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
    this.restartAutoplay();
  }

  nextSlide() {
    this.currentSlide = (this.currentSlide + 1) % this.slides.length;
    this.restartAutoplay();
  }

  goToSlide(index: number) {
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
  }

  loadHomeProducts() {
    clearTimeout(this.isIntervalActive);
    this.auth.getHomeProducts().subscribe(
      (res: any) => {
        this.productList = res.latest_collection || [];
        this.bestSellerProducts = res.best_sellers || [];
        this.callLoadHomeProducts();
        this.isLoading = false;
      },
      (error: any) => {
        console.error('Lỗi load products:', error);
        this.isLoading = false;
      }
    );
  }

  loadCustomization() {
    this.auth.getPublicCustomization().subscribe(
      (res: any) => {
        console.log('public customization response:', res);
        if (res && Array.isArray(res.slides)) {
          this.slides = res.slides.map((p: string) => p && p.startsWith('http') ? p : (p ? this.auth.getBaseUrl() + p : p)).filter(Boolean);
        }
        this.homeCollections = Array.isArray(res.collections) ? res.collections : (res.collections || []);
      },
      (err: any) => {
        console.error('Failed to load public customization:', err);
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

  handleImageError(event: any) {
    event.target.src = 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg';
  }

  test() {
    this.web3Service.getBalanceFunc('0x18E215E111aa8877266E9F8CDeDf21f605777777');
  }

  startAutoplay() {
    this.stopAutoplay();
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
}