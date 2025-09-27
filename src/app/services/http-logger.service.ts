import { HttpEvent, HttpHandler, HttpInterceptor, HttpRequest } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { AuthService } from './auth.service';

@Injectable({
  providedIn: 'root'
})
export class HttpInterceptorService implements HttpInterceptor {

  constructor(private auth: AuthService) { }
  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    var tokenReNew = localStorage.getItem('dat-shop-renew');
    let token = req.clone({
      setHeaders: {
        Authorization: `Bearer ${this.auth.getToken}`,
        Token: tokenReNew?.toString() || ''
      }
    })
    return next.handle(token);

  }
}
