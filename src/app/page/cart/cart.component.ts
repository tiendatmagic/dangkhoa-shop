import { Component } from '@angular/core';
import { DataService } from '../../services/data.service';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  selector: 'app-cart',
  standalone: false,
  templateUrl: './cart.component.html',
  styleUrl: './cart.component.scss'
})
export class CartComponent {
  cartProducts: any[] = [];
  subtotal: number = 0;
  deliveryFee: number = 0;
  total: number = 0;

  constructor(private dataService: DataService, private route: ActivatedRoute, private router: Router) {
    this.deliveryFee = this.dataService.deliveryFee;
  }

  ngOnInit() {
    const storedCart = localStorage.getItem('cartItems');
    this.cartProducts = storedCart ? JSON.parse(storedCart) : [];
    this.calculateSubtotal();
  }

  increment(product: any) {
    product.quantity++;
    this.updateLocalStorage();
    this.calculateSubtotal();
  }

  decrement(product: any) {
    if (product.quantity > 1) {
      product.quantity--;
      this.updateLocalStorage();
      this.calculateSubtotal();
    }
  }

  onQuantityChange(product: any) {
    if (product.quantity < 1) {
      product.quantity = 1;
    }
    this.updateLocalStorage();
    this.calculateSubtotal();
  }

  removeProduct(product: any) {
    const index = this.cartProducts.indexOf(product);
    if (index !== -1) {
      this.cartProducts.splice(index, 1);
      this.updateLocalStorage();
      this.calculateSubtotal();
    }
  }

  clearCart() {
    this.cartProducts = [];
    this.updateLocalStorage();
    this.calculateSubtotal();
  }

  updateLocalStorage() {
    localStorage.setItem('cartItems', JSON.stringify(this.cartProducts));
  }

  calculateSubtotal() {
    this.subtotal = this.cartProducts.reduce((sum, item) => {
      return sum + (item.price * item.quantity);
    }, 0);

    this.total = this.subtotal + this.deliveryFee;
    this.dataService.cartCount = this.cartProducts.length;
  }

  checkout() {
    this.router.navigate(['/checkout']);
    this.calculateSubtotal();
  }
}
