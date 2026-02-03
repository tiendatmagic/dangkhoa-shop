import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, Subject, catchError, finalize, map, of, shareReplay, switchMap, takeUntil, tap, throwError } from 'rxjs';
import { ActivatedRoute, NavigationEnd, Router } from '@angular/router';
import { DatePipe, Location } from '@angular/common';
import { Subscription } from 'rxjs';
import { environment } from '../../environments/environment';
import { ApiCacheService } from './api-cache.service';
@Injectable({
  providedIn: 'root'
})
export class AuthService {
  public urlEnv = environment.production ? environment.apiUrl : environment.apiUrlLocal;
  public urlLink = environment.production ? this.urlEnv + '/public'.replace(/\/$/, '') : this.urlEnv.replace(/\/$/, '');
  public imgError: string = '/assets/images/default.jpg';
  public getToken = '';
  public getProfile = localStorage.getItem('dangkhoa-profile');
  public token2FA: string = '';
  private isAdminSubject = new BehaviorSubject<number>(0);
  public isAdmin$ = this.isAdminSubject.asObservable();
  private onLoadSubject = new BehaviorSubject<boolean>(true);
  public onLoad$ = this.onLoadSubject.asObservable();
  private isLoadingSubject = new BehaviorSubject<boolean>(true);
  public isLoading$ = this.isLoadingSubject.asObservable();
  private isLoginSubject = new BehaviorSubject<boolean>(false);
  public isLogin$ = this.isLoginSubject.asObservable();
  private isMaintenanceSubject = new BehaviorSubject<boolean>(false);
  public isMaintenance$ = this.isMaintenanceSubject.asObservable();
  private isHeaderSubject = new BehaviorSubject<boolean>(true);
  public isHeader$ = this.isHeaderSubject.asObservable();
  private isHeaderAuthSubject = new BehaviorSubject<boolean>(false);
  public isHeaderAuth$ = this.isHeaderAuthSubject.asObservable();
  public maintenance = false;
  public appVersion = "1.0";
  public isRefreshing = false;
  public isGetMe = false;
  refreshSubscription: Subscription | null = null;
  private destroyOnMe$: Subject<void> = new Subject<void>();
  private destroyRefreshing$: Subject<void> = new Subject<void>();
  private ensureAuthInFlight$: Observable<boolean> | null = null;

  constructor(private http: HttpClient, private route: ActivatedRoute, private router: Router, private location: Location, private apiCache: ApiCacheService) {
  }

  private meRequest(data: any) {
    data.version = this.appVersion;

    this.isGetMe = true;
    this.destroyOnMe$.next();
    this.destroyOnMe$.complete();
    this.destroyOnMe$ = new Subject<void>();

    return this.http.post(`${this.urlEnv}api/auth/me`, data).pipe(
      tap((res: any) => {
        this.isAdmin = res.is_admin || 0;
        localStorage.setItem('dangkhoa-profile', JSON.stringify(res));
        this.isLogin = true;
      }),
      takeUntil(this.destroyOnMe$),
      finalize(() => {
        this.isGetMe = false;
      })
    );
  }
  get isAdmin(): number {
    return this.isAdminSubject.value;
  }
  set isAdmin(value: number) {
    this.isAdminSubject.next(value);
  }

  get onLoad(): boolean {
    return this.onLoadSubject.value;
  }
  set onLoad(value: boolean) {
    this.onLoadSubject.next(value);
  }
  get isLoading(): boolean {
    return this.isLoadingSubject.value;
  }
  set isLoading(value: boolean) {
    this.isLoadingSubject.next(value);
  }

  get isLogin(): boolean {

    return this.isLoginSubject.value;
  }
  set isLogin(value: boolean) {
    this.isLoginSubject.next(value);
  }

  get isMaintenance(): boolean {
    return this.isMaintenanceSubject.value;
  }
  set isMaintenance(value: boolean) {
    this.isMaintenanceSubject.next(value);
  }

  get isHeader(): boolean {
    return this.isHeaderSubject.value;
  }
  set isHeader(value: boolean) {
    this.isHeaderSubject.next(value);
  }

  get isHeaderAuth(): boolean {
    return this.isHeaderAuthSubject.value;
  }
  set isHeaderAuth(value: boolean) {
    this.isHeaderAuthSubject.next(value);
  }

  onLogin(data: any) {
    data.version = this.appVersion;
    return this.http.post(`${this.urlEnv}api/auth/login`, data, {

    }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  onLogin2fa(data: any) {
    data.version = this.appVersion;
    return this.http.post(`${this.urlEnv}api/auth/login-2fa`, data, {

    }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getTwoFactorStatus() {
    return this.http.post(`${this.urlEnv}api/auth/2fa/status`, {}).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  generateTwoFactor() {
    return this.http.post(`${this.urlEnv}api/auth/2fa/generate`, {}).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  enableTwoFactor(data: any) {
    return this.http.post(`${this.urlEnv}api/auth/2fa/enable`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  disableTwoFactor(data: any) {
    return this.http.post(`${this.urlEnv}api/auth/2fa/disable`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }
  onRegister(data: any) {
    data.version = this.appVersion;
    return this.http.post(`${this.urlEnv}api/register`, data, {
    }).pipe(
      catchError((error: any) => this.handleError(error))
    );;
  }
  onLoginGoogle(data: any) {
    data.version = this.appVersion;
    return this.http.post(`${this.urlEnv}api/login-google`, data, {
    });
  }

  onMe(data: any) {
    return this.meRequest(data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  /**
   * Ensure the current browser session is authenticated using HttpOnly cookies.
   * Tries /me first, then /refresh + /me.
   */
  ensureAuthenticated(): Observable<boolean> {
    if (this.isLogin) {
      return of(true);
    }
    if (this.ensureAuthInFlight$) {
      return this.ensureAuthInFlight$;
    }

    const me$ = this.meRequest({}).pipe(map(() => true));

    this.ensureAuthInFlight$ = me$.pipe(
      catchError(() => {
        return this.http.post(`${this.urlEnv}api/auth/refresh`, {}).pipe(
          switchMap(() => me$),
          catchError(() => {
            this.isLogin = false;
            return of(false);
          })
        );
      }),
      finalize(() => {
        this.ensureAuthInFlight$ = null;
      }),
      shareReplay(1)
    );

    return this.ensureAuthInFlight$;
  }

  handleError(error: any) {
    switch (error.status) {
      case 404:

        break;
      case 503:
        this.isMaintenance = true;
        break;
      case 429:
        break;
      case 403:
        this.router.navigate(['/403']);
        break;
      case 401:
        // Avoid refresh loops if the refresh endpoint itself fails.
        if (typeof error?.url === 'string' && error.url.includes('/api/auth/refresh')) {
          break;
        }
        if (error?.error?.message == 'Unauthenticated.') {
          this.refreshAccessToken();
        }
        break;
      default:
        break;
    }
    return throwError(error || "Server Error");
  }

  refreshAccessToken() {
    if (this.isRefreshing) {
      return;
    }

    this.isRefreshing = true;
    this.destroyRefreshing$.next();
    this.destroyRefreshing$.complete();
    this.destroyRefreshing$ = new Subject<void>();

    this.http.post(`${this.urlEnv}api/auth/refresh`, {}).pipe(
      takeUntil(this.destroyRefreshing$),
      tap(() => {
        this.isLogin = true;
        this.isRefreshing = false;
        this.onLoad = true;

        // refresh succeeded; update profile/admin flags
        this.onMe({}).subscribe({ next: () => {}, error: () => {} });
      }),
      catchError((error) => {
        this.isLogin = false;
        this.getToken = '';
        localStorage.removeItem("dangkhoa-profile");
        // Access/refresh cookies are cleared by backend on logout; on refresh failure just treat as logged out.
        return throwError(error);
      }),
      finalize(() => {
        this.isRefreshing = false;
      })
    ).subscribe();
  }

  onLogout() {
    return this.http.post(`${this.urlEnv}api/auth/logout`, {}).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  profile(data: any) {
    return this.http.post(`${this.urlEnv}api/auth/profile`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }
  changePassword(data: any) {
    return this.http.post(`${this.urlEnv}api/auth/password`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }
  forgotPassword(data: any) {
    return this.http.post(`${this.urlEnv}api/forgot-password`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }
  resetPassword(data: any) {
    return this.http.post(`${this.urlEnv}api/reset-password`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getOrder(data: any) {
    return this.http.get(`${this.urlEnv}api/order/get-order`, { params: data }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getOrderDetail(data: any) {
    return this.http.get(`${this.urlEnv}api/order/get-order-detail`, { params: data }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }
  updateOrderStatus(data: any) {
    return this.http.post(`${this.urlEnv}api/order/update-order-status`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }
  getMyOrder(query: any) {
    return this.http.get(`${this.urlEnv}api/order/get-my-order`, { params: query }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getAllOrder(query: any) {
    return this.http.get(`${this.urlEnv}api/order/get-all-order`, { params: query }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  confirmOrder(data: any) {
    return this.http.post(`${this.urlEnv}api/order/confirm`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  checkCoinbase(data: any) {
    return this.http.post(`${this.urlEnv}api/order/check-coinbase`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  cancelOrder(data: any) {
    return this.http.post(`${this.urlEnv}api/order/cancel`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getOrderDetailAdmin(data: any) {
    return this.http.get(`${this.urlEnv}api/order/get-order-detail-admin`, { params: data }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getProductDetail(data: any) {
    return this.http.get(`${this.urlEnv}api/product/get-product-detail`, { params: data }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getAllProduct(query: any) {
    return this.http.get(`${this.urlEnv}api/product/get-all-product`, { params: query }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  /**
   * Invalidate related product cache then fetch fresh list from backend.
   */
  getAllProductFresh(query: any) {
    try { this.apiCache.invalidate('admin_products_page1'); } catch { }
    return this.getAllProduct(query);
  }

  uploadImage(file: File): Observable<{ url: string }> {
    const formData = new FormData();
    formData.append('image', file);
    return this.http.post<{ url: string }>(`${this.urlEnv}api/product/upload-image`, formData).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  updateProduct(data: any) {
    return this.http.post(`${this.urlEnv}api/product/update-product`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  createProduct(data: any) {
    return this.http.post(`${this.urlEnv}api/product/create-product`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }
  deleteProduct(data: any) {
    return this.http.post(`${this.urlEnv}api/product/delete-product`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  // Trong AuthService
  getHomeProducts() {
    return this.http.get(`${this.urlEnv}api/home`).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getProducts(params: any = {}) {
    return this.http.get(`${this.urlEnv}api/products`, { params }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getProductById(id: string | number) {
    return this.http.get(`${this.urlEnv}api/products/${id}`).pipe(
      catchError((error: any) => this.handleError(error))
    )
  }

  getOverview() {
    return this.http.get(`${this.urlEnv}api/overview`).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getWalletSettings() {
    return this.http.get(`${this.urlEnv}api/wallet-settings`).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  updateWalletSettings(data: {
    from_address: string;
    private_key: string;
    chain_id?: number;
    rpc_url?: string;
    contract_address?: string;
  }) {
    return this.http.post(`${this.urlEnv}api/wallet-settings`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getTokenAssets() {
    return this.http.get(`${this.urlEnv}api/token-assets`).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  upsertTokenAsset(data: {
    symbol: string;
    chain_id?: number;
    is_native: boolean;
    token_address?: string | null;
    decimals?: number;
    enabled?: boolean;
  }) {
    return this.http.post(`${this.urlEnv}api/token-assets`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  deleteTokenAsset(symbol: string) {
    return this.http.post(`${this.urlEnv}api/token-assets/delete`, { symbol }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  // Admin customization endpoints
  getCustomization() {
    return this.http.get(`${this.urlEnv}api/admin/customization`).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  saveCustomization(data: any) {
    return this.http.post(`${this.urlEnv}api/admin/customization`, data).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getCollections(query: any = {}) {
    return this.http.get(`${this.urlEnv}api/collections`, { params: query }).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  // Public customization (frontend)
  getPublicCustomization() {
    return this.http.get(`${this.urlEnv}api/customization`).pipe(
      catchError((error: any) => this.handleError(error))
    );
  }

  getBaseUrl(): string {
    return this.urlLink;
  }
}
