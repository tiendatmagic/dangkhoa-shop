import { Component, OnDestroy } from '@angular/core';
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
  coinbaseHostedUrl: string = '';
  coinbaseQrUrl: string = '';
  private coinbaseInterval: any = null;
  coinbaseExpiresAt: Date | null = null;
  coinbaseRemaining: string = '';
  private coinbaseCountdownInterval: any = null;
  coinbaseActive: boolean = false;
  private storageListener: any = null;
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

  ngOnDestroy() {
    try {
      if (this.storageListener) window.removeEventListener('storage', this.storageListener);
    } catch (e) { }
    if (this.coinbaseCountdownInterval) clearInterval(this.coinbaseCountdownInterval);
    if (this.coinbaseInterval) clearInterval(this.coinbaseInterval);
  }

  ngOnInit() {
    this.id = this.route.snapshot.paramMap.get('id');
    if (!this.id) {
      var token = localStorage.getItem('dangkhoa-token');
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
            "order_code": res.order.order_code,
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

          // if still pending and payment method is coinbase, immediately ask server to check coinbase status
          if (res.order && res.order.status === 'pending' && res.order.payment === 'coinbase') {
            this.auth.checkCoinbase({ id: this.id }).subscribe((cres: any) => {
              if (cres.status === 'completed') {
                // refresh order and clear
                this.startOrderStatusPolling(this.id);
              } else {
                // continue polling normally
                this.startOrderStatusPolling(this.id);
              }
            }, (err) => {
              // on error, still start polling
              this.startOrderStatusPolling(this.id);
            });
          } else {
            // start polling order status after redirect from Coinbase
            this.startOrderStatusPolling(this.id);
          }
        },
        (error: any) => {
          console.error(error);
        }
      )
    }


    try {
      this.data = JSON.parse(localStorage.getItem('dangkhoa-profile') || '');
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

    // Listen to storage events so other tabs can clear QR / cart when payment completed
    this.storageListener = this.onStorageEvent.bind(this);
    window.addEventListener('storage', this.storageListener);

  }

  calculateTotal() {
    this.subtotal = this.cartProducts.reduce((sum, item) => {
      return sum + item.price * item.quantity;
    }, 0);
    this.total = this.subtotal + this.deliveryFee;
  }

  choosePayment(payment: number) {
    // reset active coinbase QR when user intentionally switches payment method
    if (this.choosePaymentMethod !== payment) {
      this.coinbaseActive = false;
      this.coinbaseHostedUrl = '';
      this.coinbaseQrUrl = '';
      this.coinbaseExpiresAt = null;
      this.coinbaseRemaining = '';
      if (this.coinbaseCountdownInterval) clearInterval(this.coinbaseCountdownInterval);
    }
    this.choosePaymentMethod = payment;
    if (payment == 2) {
      this.web3Service.connectWallet();
    }
  }

  onStorageEvent(ev: StorageEvent) {
    try {
      if (!ev.key) return;
      // other tab cleared cart or marked order completed
      if (ev.key.startsWith('order_status_')) {
        const val = ev.newValue;
        if (val === 'completed') {
          // clear local QR and countdown
          this.coinbaseHostedUrl = '';
          this.coinbaseQrUrl = '';
          this.coinbaseExpiresAt = null;
          this.coinbaseRemaining = '';
          if (this.coinbaseCountdownInterval) clearInterval(this.coinbaseCountdownInterval);
          this.coinbaseActive = false;
        }
      }

      if (ev.key === 'cartItems') {
        const cv = ev.newValue || '[]';
        try {
          const arr = JSON.parse(cv);
          if (Array.isArray(arr) && arr.length === 0) {
            this.coinbaseHostedUrl = '';
            this.coinbaseQrUrl = '';
            this.coinbaseExpiresAt = null;
            this.coinbaseRemaining = '';
            this.coinbaseActive = false;
            if (this.coinbaseCountdownInterval) clearInterval(this.coinbaseCountdownInterval);
          }
        } catch (e) { }
      }
    } catch (e) { }
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
    if (this.coinbaseActive) return;

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
    if (this.choosePaymentMethod == 3) {
      this.isProccessing = true;
      var data = {
        data: orderData,
        payment: 'coinbase'
      };

      this.auth.confirmOrder(data).subscribe(
        (res: any) => {
          this.isProccessing = false;
          if (res.success && res.success === 'coinbase_charge_created') {
            this.coinbaseHostedUrl = res.hosted_url;
            this.coinbaseQrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(res.hosted_url)}`;
            if (res.expires_at) {
              this.coinbaseExpiresAt = new Date(res.expires_at);
              this.startCoinbaseCountdown();
            }
            // mark that QR is active to prevent multiple creates
            this.coinbaseActive = true;
            this.startCoinbasePolling(res.order_id);
          } else {
            this.snackBar.open('Failed to create coinbase charge.', 'OK', { duration: 3000 });
          }
        },
        (error: any) => {
          this.isProccessing = false;
          this.snackBar.open('Order failed.', 'OK', { duration: 3000 });
        }
      );

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

  startCoinbasePolling(orderId: string) {
    if (this.coinbaseInterval) clearInterval(this.coinbaseInterval);
    this.coinbaseInterval = setInterval(() => {
      this.auth.getOrder({ id: orderId }).subscribe((res: any) => {
        const status = res.order ? res.order.status : null;
        if (status && status !== 'pending') {
          clearInterval(this.coinbaseInterval);
          this.router.navigate(['/checkout', orderId]);
        }
      }, (err) => {
        // ignore
      });
    }, 5000);
  }

  startOrderStatusPolling(orderId: string) {
    const interval = setInterval(() => {
      this.auth.getOrder({ id: orderId }).subscribe((res: any) => {
        if (!res.order) return;
        const status = res.order.status;
        this.orderData = {
          "id": res.order.id,
          "order_code": res.order.order_code,
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
          "status": status
        };

        if (status && status !== 'pending') {
          clearInterval(interval);
          if (status === 'completed') {
            // clear local cart and notify other tabs
            try {
              localStorage.setItem('cartItems', JSON.stringify([]));
              localStorage.setItem('order_status_' + orderId, 'completed');
            } catch (e) { }
            this.dataService.cartCount = 0;
            this.dataService.removeCart();
            // clear coinbase UI
            this.coinbaseHostedUrl = '';
            this.coinbaseQrUrl = '';
            this.coinbaseExpiresAt = null;
            this.coinbaseRemaining = '';
            this.coinbaseActive = false;
          }
        }
      }, (err) => {
        // ignore
      });
    }, 4000);
  }

  startCoinbaseCountdown() {
    if (this.coinbaseCountdownInterval) clearInterval(this.coinbaseCountdownInterval);
    const update = () => {
      if (!this.coinbaseExpiresAt) return;
      const now = new Date().getTime();
      const exp = this.coinbaseExpiresAt.getTime();
      let diff = Math.max(0, Math.floor((exp - now) / 1000));
      if (diff <= 0) {
        this.coinbaseRemaining = 'Expired';
        this.coinbaseHostedUrl = '';
        this.coinbaseQrUrl = '';
        this.coinbaseActive = false;
        clearInterval(this.coinbaseCountdownInterval);
        return;
      }
      const minutes = Math.floor(diff / 60);
      const seconds = diff % 60;
      this.coinbaseRemaining = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    };
    update();
    this.coinbaseCountdownInterval = setInterval(update, 1000);
  }

  handleImageError(event: any) {
    event.target.src = 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg';
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

