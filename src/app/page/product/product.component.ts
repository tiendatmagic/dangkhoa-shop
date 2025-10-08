import { Component, OnInit, OnDestroy } from '@angular/core';
import { DataService } from '../../services/data.service';
import { ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AuthService } from '../../services/auth.service';

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
    private snackBar: MatSnackBar
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
        this.selectedImage = this.productData.image[0] || null;

        const homeRes: any = await this.auth.getHomeProducts().toPromise();
        this.productList = homeRes.latest_collection || [];
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
      this.snackBar.open('Please select size and quantity', 'OK', {
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
}