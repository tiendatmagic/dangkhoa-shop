import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { DataService } from '../../../services/data.service';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../../services/auth.service';
import { AdminTabService } from '../../../services/admin-tab.service';
import { ApiCacheService } from '../../../services/api-cache.service';
import { Subject, of } from 'rxjs';
import { catchError, debounceTime, distinctUntilChanged, switchMap, takeUntil } from 'rxjs/operators';

@Component({
  selector: 'app-admin-product',
  standalone: false,
  templateUrl: './admin-product.component.html',
  styleUrl: './admin-product.component.scss'
})
export class AdminProductComponent implements OnInit, OnDestroy {
  productData: any;
  isLoading: boolean = false;
  per_page: number = 10;
  page: number = 1;
  totalProducts: number = 0;
  urlLink: string = '';
  private destroy$ = new Subject<void>();
  constructor(private route: ActivatedRoute, private router: Router, private dataService: DataService, private http: HttpClient, private auth: AuthService, private adminTab: AdminTabService, private apiCache: ApiCacheService) {
  }

  ngOnInit() {
    this.urlLink = this.auth.getBaseUrl();
    // subscribe to tab changes and cancel previous requests
    this.adminTab.activeTab$.pipe(
      distinctUntilChanged(),
      debounceTime(150),
      switchMap(tab => {
        if (tab === 'product') {
          this.isLoading = true;
          return this.apiCache.getCached('admin_products_page1', this.auth.getAllProduct({ page: 1 }).pipe(catchError(() => of({ data: [], total: 0 }))));
        }
        return of(null);
      }),
      takeUntil(this.destroy$)
    ).subscribe((res: any) => {
      if (!res) return;
      this.productData = res.data;
      this.totalProducts = res.total;
      this.isLoading = false;
    }, (err: any) => {
      console.error(err);
      this.isLoading = false;
    });
  }

  viewMore() {

    if (this.isLoading) return;

    this.isLoading = true;
    this.auth.getAllProduct({ per_page: this.per_page += 10 }).pipe(takeUntil(this.destroy$)).subscribe((res: any) => {
      this.productData = res.data;
      this.totalProducts = res.total;
      this.isLoading = false;
    }, (error: any) => {
      console.error(error);
      this.isLoading = false;
    });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  handleImageError(event: any) {
    event.target.src = 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg';
  }

  deleteProduct(id: any) {
    const confirmDelete = window.confirm('Are you sure you want to delete this product? This action cannot be undone.');

    if (confirmDelete) {
      this.isLoading = true;
      this.auth.deleteProduct({ id: id }).subscribe(
        (res: any) => {
          this.isLoading = false;
          this.dataService.showNotify('Success', 'Delete successfully', 'success', true, true, false);
          this.router.navigate(['/admin']);
        },
        (error: any) => {
          console.error(error);
          this.isLoading = false;
          this.dataService.showNotify('Error', 'Delete failed: ' + (error.error?.message || 'Unknown error'), 'error', true, true, false);
        }
      );
    } else {
      this.dataService.showNotify('Error', 'Delete cancelled', 'error', true, true, false);
    }
  }
}
