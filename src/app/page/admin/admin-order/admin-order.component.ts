import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { DataService } from '../../../services/data.service';
import { AuthService } from '../../../services/auth.service';
import { AdminTabService } from '../../../services/admin-tab.service';
import { ApiCacheService } from '../../../services/api-cache.service';
import { Subject, of } from 'rxjs';
import { catchError, debounceTime, distinctUntilChanged, switchMap, takeUntil } from 'rxjs/operators';
@Component({
  selector: 'app-admin-order',
  standalone: false,
  templateUrl: './admin-order.component.html',
  styleUrl: './admin-order.component.scss'
})
export class AdminOrderComponent implements OnInit, OnDestroy {
  deliveryFee: number = 0;
  orderData: any;
  isLoading: boolean = false;
  per_page: number = 10;
  page: number = 1;
  totalOrders: number = 0;
  private destroy$ = new Subject<void>();
  private ordersSub: any = null;

  constructor(private route: ActivatedRoute, private router: Router, private dataService: DataService, private http: HttpClient, private auth: AuthService, private adminTab: AdminTabService, private apiCache: ApiCacheService) {
    this.deliveryFee = this.dataService.deliveryFee;
  }

  ngOnInit() {
    this.adminTab.activeTab$.pipe(
      distinctUntilChanged(),
      debounceTime(150),
      switchMap(tab => {
        if (tab === 'orders') {
          this.isLoading = true;
          return this.apiCache.getCached('admin_orders_page1', this.auth.getAllOrder({ page: 1 }).pipe(catchError(() => of({ data: [], total: 0 }))));
        }
        return of(null);
      }),
      takeUntil(this.destroy$)
    ).subscribe((res: any) => {
      if (!res) return;
      this.orderData = res.data;
      this.totalOrders = res.total;
      this.isLoading = false;
    }, (err: any) => {
      console.error(err);
      this.isLoading = false;
    });
  }

  viewMore() {

    if (this.isLoading) return;

    this.isLoading = true;
    // cancel previous orders request and start a new one (increase per_page)
    const params = { per_page: this.per_page += 10 };
    this.fetchOrders(params);
  }

  fetchOrders(params: any) {
    if (this.ordersSub) {
      try { this.ordersSub.unsubscribe(); } catch { }
      this.ordersSub = null;
    }
    this.isLoading = true;
    // use cache for page=1, otherwise direct call
    if (params && params.page === 1) {
      this.ordersSub = this.apiCache.getCached('admin_orders_page1', this.auth.getAllOrder({ page: 1 })).pipe(takeUntil(this.destroy$)).subscribe((res: any) => {
        this.orderData = res.data;
        this.totalOrders = res.total;
        this.isLoading = false;
      }, (err: any) => {
        console.error(err);
        this.isLoading = false;
      });
    } else {
      this.ordersSub = this.auth.getAllOrder(params).pipe(takeUntil(this.destroy$)).subscribe((res: any) => {
        this.orderData = res.data;
        this.totalOrders = res.total;
        this.isLoading = false;
      }, (err: any) => {
        console.error(err);
        this.isLoading = false;
      });
    }
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}