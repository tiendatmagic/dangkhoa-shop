import { Component, OnInit, OnDestroy } from '@angular/core';
import { DataService } from '../../services/data.service';
import { ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AuthService } from '../../services/auth.service';
import { DomSanitizer } from '@angular/platform-browser';

@Component({
  selector: 'app-product',
  standalone: false,
  templateUrl: './product.component.html',
  styleUrl: './product.component.scss'
})
export class ProductComponent implements OnInit, OnDestroy {
  productData: any;
  selectedSize: string | null = null;
  selectedImage: string | null = null;
  public id: any;
  productList: any[] = [];
  quantity: number = 1;
  loading: boolean = false;
  error: any | null = null;

  public routeSubscription: Subscription | undefined;

  constructor(
    private auth: AuthService,
    private dataService: DataService,
    private route: ActivatedRoute,
    private snackBar: MatSnackBar,
    private sanitizer: DomSanitizer
  ) { }

  async ngOnInit() {
    this.routeSubscription = this.route.paramMap.subscribe(async params => {
      window.scrollTo(0, 0);
      this.selectedImage = null;
      this.selectedSize = null;
      this.quantity = 1;
      this.loading = true;
      this.error = null;
      this.id = params.get('id');

      try {
        const productRes: any = await this.auth.getProductById(this.id).toPromise();
        this.productData = productRes.product || productRes;
        if (!this.productData) {
          throw new Error(`Product with ID ${this.id} not found`);
        }

        this.productData.image = this.productData.image.map((img: string) => this.auth.getBaseUrl() + img);
        this.selectedImage = this.productData.image[0] || null;

        const homeRes: any = await this.auth.getHomeProducts().toPromise();
        this.productList = homeRes.latest_collection || [];
        this.productList.forEach((product: any) => {
          product.image = product.image.map((img: string) => this.auth.getBaseUrl() + img);
        });
        if (this.productData.size.length == 1) {
          this.selectedSize = this.productData.size[0];
        }
      } catch (e: any) {
        console.error(e);
        this.error = e.message || 'Failed to load product data';
        this.snackBar.open(this.error, 'OK', {
          duration: 3000,
          horizontalPosition: 'right',
          verticalPosition: 'bottom',
        });
      } finally {
        this.loading = false;
      }
    });
  }

  handleImageError(event: any) {
    event.target.src = 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg';
  }

  sanitizeHtml(html: string) {
    return this.sanitizer.bypassSecurityTrustHtml(html);
  }

  ngOnDestroy() {
    if (this.routeSubscription) {
      this.routeSubscription.unsubscribe();
    }
  }

  selectSize(size: string) {
    this.selectedSize = size;
  }

  selectImage(image: string) {
    this.selectedImage = image;
  }

  addToCart() {
    const data = {
      id: this.id,
      name: this.productData.name,
      image: this.productData.image[0],
      price: Number(this.productData.price),
      quantity: Number(this.quantity),
      size: this.selectedSize
    };

    if (!data.id || !data.size || data.quantity <= 0) {
      this.snackBar.open('Please select type and quantity', 'OK', {
        duration: 3000,
        horizontalPosition: 'right',
        verticalPosition: 'bottom',
      });
      return;
    }

    this.dataService.addToCart(data);

    this.snackBar.open('Product added to cart successfully!', 'OK', {
      duration: 3000,
      horizontalPosition: 'right',
      verticalPosition: 'bottom',
    });
  }

  increment() {
    this.quantity++;
  }

  decrement() {
    if (this.quantity > 1) {
      this.quantity--;
    }
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