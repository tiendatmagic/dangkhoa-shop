import { Component } from '@angular/core';
import { initFlowbite } from 'flowbite';
import { TranslateService } from '@ngx-translate/core';
import { DataService } from './services/data.service';
import { AuthService } from './services/auth.service';
import { ApiCacheService } from './services/api-cache.service';
import { NavigationEnd, Router } from '@angular/router';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  standalone: false,
  styleUrl: './app.component.scss'
})
export class AppComponent {
  title = 'dangkhoa_shop';
  cartItems: any[] = [];

  constructor(public translate: TranslateService, private router: Router, private dataService: DataService, private auth: AuthService, private apiCache: ApiCacheService) {
    this.auth.onLoad$.subscribe((value) => {
      if (value) {
        var token = localStorage.getItem('dangkhoa-token');
        if (token) {

          this.auth.onMe({}).subscribe((res: any) => {
            this.auth.isLogin = true;
          },
            (error: any) => {
              if (error.status == 0 && error.statusText == 'Unknown Error') {
                localStorage.removeItem("dangkhoa-renew");
                localStorage.removeItem("dangkhoa-token");
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
    // ensure cache cleared on app bootstrap (handles reloads / dev HMR)
    try { this.apiCache.invalidateAll(); } catch {}
    var token = localStorage.getItem('dangkhoa-token');
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
