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
    // Bootstrap session using HttpOnly cookies (no localStorage tokens).
    this.auth.ensureAuthenticated().subscribe({
      next: () => { this.auth.onLoad = true; },
      error: () => { this.auth.onLoad = true; }
    });
  }
  ngOnInit(): void {
    // ensure cache cleared on app bootstrap (handles reloads / dev HMR)
    try { this.apiCache.invalidateAll(); } catch {}
    const accessPaths = ['my', 'account', 'dashboard', 'admin', 'deposit', 'withdraw', 'event', 'wallet', 'profile', 'order', 'checkout', 'order-detail'];

    this.router.events.subscribe((event: any) => {
      if (event instanceof NavigationEnd) {
        const firstPath = event.url.split('/')[1];

        if (accessPaths.includes(firstPath)) {
          if (!this.auth.isLogin) {
            this.auth.ensureAuthenticated().subscribe((ok) => {
              if (!ok) this.router.navigate(['/login']);
            });
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
