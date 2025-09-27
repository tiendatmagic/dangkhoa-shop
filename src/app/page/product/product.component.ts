import { Component, OnInit, OnDestroy } from '@angular/core';
import { DataService } from '../../services/data.service';
import { ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';
import { MatSnackBar } from '@angular/material/snack-bar';

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

  public routeSubscription: Subscription | undefined;

  constructor(private dataService: DataService, private route: ActivatedRoute, private snackBar: MatSnackBar) { }

  ngOnInit() {
    this.routeSubscription = this.route.paramMap.subscribe(params => {
      window.scrollTo(0, 0);
      this.selectedImage = null;
      this.selectedSize = null;
      this.quantity = 1;
      this.id = params.get('id');
      this.productList = this.dataService.productList;
      this.productData = this.dataService.productList.find(product => product.id == this.id);
      if (!this.productData) {
        console.error(`Product with ID ${this.id} not found`);
      } else {
        this.selectedImage = this.productData.image[0] || null;
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
      })
      return;
    }

    this.dataService.addToCart(data);

    this.snackBar.open('Product added to cart successfully!', 'OK', {
      duration: 3000,
      horizontalPosition: 'right',
      verticalPosition: 'bottom',
    })
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