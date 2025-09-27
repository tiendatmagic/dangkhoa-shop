import { Component } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ActivatedRoute, Router } from '@angular/router';
import { DataService } from '../../services/data.service';
import { Web3Service } from '../../services/web3.service';
import { combineLatest } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { FormGroup } from '@angular/forms';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-place-order',
  standalone: false,
  templateUrl: './place-order.component.html',
  styleUrl: './place-order.component.scss'
})

export class PlaceOrderComponent {
  choosePaymentMethod: number = 1;
  cartProducts: any[] = [];
  subtotal: number = 0;
  deliveryFee: number = 0;
  total: number = 0;
  public id: any;
  name: string = '';
  email: string = '';
  address: string = '';
  phone: string = '';
  note: string = '';
  data: any;
  account: string = '';
  balance: any;
  USDTBalance: any;
  nativeSymbol: string = '';
  isConnected: boolean = false;
  selectedNetwork: string = '0x38';
  isProccessing: boolean = false;
  orderData: any;


  constructor(private snackBar: MatSnackBar, private route: ActivatedRoute, private router: Router, private dataService: DataService, private web3Service: Web3Service, private http: HttpClient, private auth: AuthService) {
    this.deliveryFee = this.dataService.deliveryFee;
  }

  ngOnInit() {
    this.id = this.route.snapshot.paramMap.get('id');
    if (!this.id) {
      var token = localStorage.getItem('dat-shop-token');
      if (!token) {
        this.snackBar.open('Please login first', 'OK', {
          duration: 3000,
          horizontalPosition: 'right',
          verticalPosition: 'bottom',
        })
        this.router.navigate(['/login']);
      }

      try {
        const storedCart = localStorage.getItem('cartItems');
        this.cartProducts = storedCart ? JSON.parse(storedCart) : [];
        if (!this.cartProducts.length) {
          this.router.navigate(['/cart']);
        }
      } catch (error) {
        localStorage.setItem('cartItems', JSON.stringify([]));
      }
      this.calculateTotal();
    }
    else {
      this.auth.getOrder({ id: this.id }).subscribe(
        (res: any) => {
          this.orderData = {
            "id": res.order.id,
            "name": res.order.full_name || res.order.name,
            "email": res.order.email,
            "phone": res.order.phone,
            "address": res.order.address,
            "note": res.order.note,
            "paymentMethod": res.order.payment,
            "txhash": res.order.txhash,
            "items": res.items,
            "total": res.total,
            "created_at": res.order.created_at,
            "status": res.order.status
          }
        },
        (error: any) => {
          console.error(error);
        }
      )
    }


    try {
      this.data = JSON.parse(localStorage.getItem('dat-shop-profile') || '');
      this.name = this.data.full_name;
      this.email = this.data.email;
      this.address = this.data.address;
      this.phone = this.data.phone;
    } catch (error) {
      this.auth.onLoad = true;
    }


    combineLatest([
      this.web3Service.account$,
      this.web3Service.balance$,
      this.web3Service.USDTBalance$,
      this.web3Service.nativeSymbol$,
      this.web3Service.isConnected$,
      this.web3Service.chainId$
    ]).subscribe(([account, balance, USDTBalance, nativeSymbol, isConnected, chainId]) => {
      this.account = account;
      this.balance = balance;
      this.USDTBalance = USDTBalance;
      this.nativeSymbol = nativeSymbol;
      this.isConnected = isConnected;
      this.selectedNetwork = chainId;
    });

  }

  calculateTotal() {
    this.subtotal = this.cartProducts.reduce((sum, item) => {
      return sum + item.price * item.quantity;
    }, 0);
    this.total = this.subtotal + this.deliveryFee;
  }

  choosePayment(payment: number) {
    this.choosePaymentMethod = payment;
    if (payment == 2) {
      this.web3Service.connectWallet();
    }
  }

  async proceedToPayment() {
    const orderData = {
      name: this.name,
      email: this.email,
      address: this.address,
      phone: this.phone,
      note: this.note,
      paymentMethod: this.choosePaymentMethod === 1 ? 'Cash on delivery' : 'USDT',
      cart: this.cartProducts,
      subtotal: this.subtotal,
      deliveryFee: this.deliveryFee,
      total: this.total
    };

    if (this.isProccessing) return;

    if (!orderData.name || !orderData.email || !orderData.address || !orderData.phone) {
      this.snackBar.open('Please fill in all the required fields.', 'OK', {
        duration: 3000,
        horizontalPosition: 'right',
        verticalPosition: 'bottom',
      })
      return;
    }

    this.auth.onLoad = true;

    if (this.choosePaymentMethod == 2) {
      const tokenAddress = this.dataService.usdtAddress;
      const merchantAddress = this.dataService.merchantAddress;

      this.isProccessing = true;
      await this.web3Service.transferUSDT(tokenAddress, merchantAddress, this.total, 18)
        .then((receipt: any) => {
          var data = {
            data: {
              ...orderData,
              transactionHash: receipt,
              amount: this.total,
              from: this.account,
              to: merchantAddress,
            },
            payment: 'usdt'
          }

          this.auth.confirmOrder(data).subscribe(
            (res: any) => {
              this.snackBar.open('Order placed successfully!', 'OK', {
                horizontalPosition: 'right',
                verticalPosition: 'bottom',
                duration: 3000
              });
              if (res.success) {
                this.router.navigate(['/checkout', res.order_id]);
                this.dataService.cartCount = 0;
                this.isProccessing = false;
                this.dataService.removeCart();
              }
            },
            (error: any) => {
              this.isProccessing = false;
              this.snackBar.open('Order failed.', 'OK', {
                horizontalPosition: 'right',
                verticalPosition: 'bottom',
                duration: 3000
              });
            }
          )
        })
        .catch((err: any) => {
          this.isProccessing = false;
          this.snackBar.open('Payment failed.', 'OK', {
            horizontalPosition: 'right',
            verticalPosition: 'bottom', duration: 3000
          });
          console.error(err);
          if (err && err.code != 100) {
            this.router.navigate(['/cart']);
          }
        });

      return;
    }
    else {
      this.isProccessing = true;
      var data = {
        data: orderData,
        payment: 'cash'
      };
      this.auth.confirmOrder(data).subscribe(
        (res: any) => {
          this.snackBar.open('Order placed successfully!', 'OK', {
            horizontalPosition: 'right',
            verticalPosition: 'bottom',
            duration: 3000
          });
          if (res.success) {
            this.router.navigate(['/checkout', res.order_id]);
            this.dataService.cartCount = 0;
            this.isProccessing = false;
            this.dataService.removeCart();
          }
        },
        (error: any) => {
          this.isProccessing = false;
          this.snackBar.open('Order failed.', 'OK', {
            horizontalPosition: 'right',
            verticalPosition: 'bottom',
            duration: 3000
          });
          console.error(error);
        }
      )
    }
  }

  viewOrder() {
    this.router.navigate(['/order-detail', this.id]);
  }

  disconnectWallet() {
    if (this.isProccessing) return;
    this.web3Service.disconnectWallet();
  }

  viewOnBSCScan(tx: string) {
    window.open(`https://bscscan.com/tx/${tx}`, '_blank');
  }
  copyAddress(address: string): void {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(address).then(() => {
        console.log('Address copied to clipboard');
        this.snackBar.open('Address copied to clipboard', 'OK', {
          horizontalPosition: 'right',
          verticalPosition: 'bottom',
          duration: 3000
        });
      }).catch((error) => {
        console.error('Failed to copy address: ', error);
      });
    } else {
      let textArea = document.createElement("textarea");
      textArea.value = address;
      document.body.appendChild(textArea);
      textArea.select();
      try {
        document.execCommand('copy');
      } catch (error) {
        console.error('Failed to copy address: ', error);
      }
      document.body.removeChild(textArea);
    }
  }
}

