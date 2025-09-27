import { Injectable } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { BehaviorSubject } from 'rxjs';
import { NotifyComponent } from '../modal/notify/notify.component';
import { NotifyModalComponent } from '../modal/notify-modal/notify-modal.component';

@Injectable({
  providedIn: 'root'
})
export class DataService {
  productList: any[] = [
    {
      id: 1,
      name: 'Football Jersey',
      price: 5,
      image: ['https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063577/b1devpuovnvqwdo8pfia.jpg', 'https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063577/o7kcxzpkbvobb05iu949.jpg', 'https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063577/iwopzvmpc5olrt7anheh.jpg'],
      size: ['S', 'M', 'XL', 'XXL'],
      category: 'men',
      isBestSeller: true,
    },
    {
      id: 2,
      name: 'Hampton Long Sleeve Shirt',
      price: 50,
      image: ['https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063302/wowbtd0tlj6tpfxvkxcg.jpg', 'https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063302/kjfejg6lvpqwib89qwfy.jpg'],
      size: ['S', 'M', 'L'],
      category: 'men',
      isBestSeller: false,
    },
    {
      id: 3,
      name: 'Cropped Fit Graphic T-Shirt',
      price: 30,
      image: ['https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063623/gnttim2dl0vxljmpejlu.jpg', 'https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063623/fwtx0axdz5iowwgarff7.jpg'],
      size: ['S', 'M', 'XL', 'XXL', 'L'],
      category: 'men',
      isBestSeller: false,
    },
    {
      id: 4,
      name: 'Easy Short',
      price: 10,
      image: ['https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063667/qudc6bwwlvnzfsgmx2ah.jpg', 'https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063667/ocu1bqsqtipqrk1dt61i.jpg'],
      size: ['S', 'M', 'L'],
      category: 'men',
      isBestSeller: false,
    },
    {
      id: 5,
      name: 'Alina Shirred Halter Top',
      price: 35,
      image: ['https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063762/ggjerydhpsewxvm8kbyd.jpg', 'https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063762/jqoik9izii3hv5j2z2b4.jpg'],
      size: ['S', 'M', 'L', 'XL'],
      category: 'women',
      isBestSeller: true,
    },
    {
      id: 6,
      name: 'Mikki Drop Hem Mini Dress',
      price: 60,
      image: ['https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063815/hbrtbquoemjpaghclydx.jpg', 'https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063815/hk4ofjpoxe8ocrfvagkz.jpg'],
      size: ['S', 'M', 'L', 'XL'],
      category: 'women',
      isBestSeller: true,
    },
    {
      id: 7,
      name: 'Haven Wide Leg Pant',
      price: 30,
      image: ['https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063885/cnffks7xikrhgjan9zil.jpg', 'https://res.cloudinary.com/dfyykwzsa/image/upload/v1753063885/e6xpjggzveoxjospfxos.jpg'],
      size: ['S', 'M', 'L', 'XL'],
      category: 'women',
      isBestSeller: false,
    },
    {
      id: 8,
      name: 'Kaia Faux Leather Bomber',
      price: 109,
      image: ['https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064020/zc0xgahhdyl4jttwci2e.jpg', 'https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064020/mwzlfoxgfofmncknwecv.jpg'],
      size: ['S', 'M', 'L', 'XL', 'XXL'],
      category: 'women',
      isBestSeller: true,
    },
    {
      id: 9,
      name: 'Sammy Oversize Hoodie',
      price: 34,
      image: ['https://res.cloudinary.com/dfyykwzsa/image/upload/v1753064102/ri8zbzu6eq3z2ajkrwff.jpg'],
      size: ['S', 'M', 'L',],
      category: 'kids',
      isBestSeller: false,
    }

  ]
  cartItems: any[] = [];
  deliveryFee: number = 1;
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
