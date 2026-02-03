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
    // Use HttpOnly cookies for auth; ensure cookies are sent for API calls.
    const isApiRequest = typeof req.url === 'string' && req.url.startsWith(this.auth.urlEnv);
    const cloned = isApiRequest ? req.clone({ withCredentials: true }) : req;
    return next.handle(cloned);

  }
}
