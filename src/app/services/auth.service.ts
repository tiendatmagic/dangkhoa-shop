import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, Subject, catchError, finalize, takeUntil, tap, throwError } from 'rxjs';
import { ActivatedRoute, NavigationEnd, Router } from '@angular/router';
import { DatePipe, Location } from '@angular/common';
import { Subscription } from 'rxjs';
import { environment } from '../../environments/environment';
@Injectable({
  providedIn: 'root'
})
export class AuthService {
  public urlEnv = environment.production ? environment.apiUrl : environment.apiUrlLocal;
  public imgError: string = '/assets/images/default.jpg';
  public getToken = localStorage.getItem('dangkhoa-token');
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

  constructor(private http: HttpClient, private route: ActivatedRoute, private router: Router, private location: Location) {
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
    data.version = this.appVersion;

    if (this.isGetMe) {
      return throwError('');
    }
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
      catchError((error: any) => this.handleError(error)),
      finalize(() => {
        // this.isGetMe = false;
      })
    );
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
        if (error.error.message == 'Unauthenticated.') {
          this.refreshAccessToken();
        }
        break;
      default:
        if (localStorage.getItem("dangkhoa-token")) {

        }
        break;
    }
    return throwError(error || "Server Error");
  }

  refreshAccessToken() {
    const refreshToken = localStorage.getItem('dangkhoa-renew');

    if (!refreshToken || this.isRefreshing) {
      return;
    }

    this.isRefreshing = true;
    this.destroyRefreshing$.next();
    this.destroyRefreshing$.complete();
    this.destroyRefreshing$ = new Subject<void>();

    this.http.post(`${this.urlEnv}api/auth/refresh`, { refresh_token: refreshToken }).pipe(
      takeUntil(this.destroyRefreshing$),
      tap((response: any) => {
        this.getToken = response.access_token;
        this.isLogin = true;
        this.isRefreshing = false;
        localStorage.setItem('dangkhoa-token', response.access_token);
        localStorage.setItem('dangkhoa-renew', response.refresh_token);
        this.onLoad = true;
      }),
      catchError((error) => {
        const allowedPaths = ['login', 'register', 'forgot-password', 'reset-password', 'verify-2fa'];
        const currentUrl = this.location.path();

        if (localStorage.getItem("dangkhoa-token")) {
          if (!allowedPaths.some(path => currentUrl.includes(path))) {
          }
        }
        this.isLogin = false;
        this.getToken = '';
        localStorage.removeItem("dangkhoa-profile");
        localStorage.removeItem("dangkhoa-renew");
        localStorage.removeItem("dangkhoa-token");
        this.router.navigate(['/login']);
        return throwError(error);
      }),
      finalize(() => {
        this.isRefreshing = false;
      })
    ).subscribe();
  }

  onLogout() {
    const refreshToken = localStorage.getItem('dangkhoa-renew');
    return this.http.post(`${this.urlEnv}api/auth/logout`, { refresh_token: refreshToken }).pipe(
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

  uploadImage(file: File): Observable<{ url: string }> {
    const formData = new FormData();
    formData.append('image', file);  // Key 'image' tùy backend, có thể là 'file'
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


}
