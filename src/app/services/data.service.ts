import { Injectable } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { BehaviorSubject } from 'rxjs';
import { NotifyComponent } from '../modal/notify/notify.component';
import { NotifyModalComponent } from '../modal/notify-modal/notify-modal.component';

@Injectable({
  providedIn: 'root'
})
export class DataService {

  cartItems: any[] = [];
  deliveryFee: number = 0;
  usdtAddress = '0x55d398326f99059fF775485246999027B3197955';
  merchantAddress = '0x1AD11e0e96797a14336Bf474676EB0A332055555';

  private cartCountSubject = new BehaviorSubject<number>(0);
  public cartCount$ = this.cartCountSubject.asObservable();

  constructor(public dialog: MatDialog) { }

  get cartCount(): number {
    return this.cartCountSubject.value;
  }
  set cartCount(value: number) {
    this.cartCountSubject.next(value);
  }

  addToCart(product: any) {
    var getCartItems = localStorage.getItem('cartItems');

    if (getCartItems) {
      try {
        this.cartItems = JSON.parse(getCartItems);
      } catch (error) {
        this.cartItems = [];
      }
    }
    this.cartItems.unshift(product);
    localStorage.setItem('cartItems', JSON.stringify(this.cartItems));
    this.cartCount = this.cartItems.length;
  }

  removeCart() {
    this.cartItems = [];
    localStorage.setItem('cartItems', JSON.stringify(this.cartItems));
    this.cartCount = this.cartItems.length;
  }

  showNotify(
    title: string,
    message: string,
    status: string,
    showCloseBtn: boolean = true,
    disableClose: boolean = true,
    installMetamask: boolean = false
  ) {
    this.dialog.closeAll();
    this.dialog.open(NotifyModalComponent, {
      disableClose: disableClose,
      width: '90%',
      maxWidth: '400px',
      data: {
        title: title,
        message: message,
        status: status,
        showCloseBtn: showCloseBtn,
        installMetamask: installMetamask,
      },
    });
  }
}
