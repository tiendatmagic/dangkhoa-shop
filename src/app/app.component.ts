import { Component } from '@angular/core';
import { initFlowbite } from 'flowbite';
import { TranslateService } from '@ngx-translate/core';
import { DataService } from './services/data.service';
import { AuthService } from './services/auth.service';
import { NavigationEnd, Router } from '@angular/router';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  standalone: false,
  styleUrl: './app.component.scss'
})
export class AppComponent {
  title = 'dat_shop';
  cartItems: any[] = [];

  constructor(public translate: TranslateService, private router: Router, private dataService: DataService, private auth: AuthService) {
    this.auth.onLoad$.subscribe((value) => {
      if (value) {
        var token = localStorage.getItem('dat-shop-token');
        if (token) {

          this.auth.onMe({}).subscribe((res: any) => {
            this.auth.isLogin = true;
          },
            (error: any) => {
              if (error.status == 0 && error.statusText == 'Unknown Error') {
                localStorage.removeItem("dat-shop-renew");
                localStorage.removeItem("dat-shop-token");
              }
            }
          );
        }
        else {
          this.auth.refreshAccessToken();
        }
      }
    });
  }
  ngOnInit(): void {
    var token = localStorage.getItem('dat-shop-token');
    const accessPaths = ['my', 'account', 'dashboard', 'admin', 'deposit', 'withdraw', 'event', 'wallet', 'profile', 'order', 'checkout', 'order-detail'];

    this.router.events.subscribe((event: any) => {
      if (event instanceof NavigationEnd) {
        const firstPath = event.url.split('/')[1];

        if (accessPaths.includes(firstPath)) {
          if (!this.auth.isLogin && !token) {
            this.router.navigate(['/login']);
          }
        }
      }
    });
    //
    var getCartItems = localStorage.getItem('cartItems');

    if (getCartItems) {
      try {
        this.cartItems = JSON.parse(getCartItems);
      } catch (error) {
        this.cartItems = [];
      }
    }
    this.dataService.cartCount = this.cartItems.length;
    initFlowbite();
  }
}
