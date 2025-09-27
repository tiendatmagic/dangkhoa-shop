import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { HeaderComponent } from './layout/header/header.component';
import { MainComponent } from './layout/main/main.component';
import { FooterComponent } from './layout/footer/footer.component';
import { MatSnackBarModule } from '@angular/material/snack-bar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { MAT_DATE_LOCALE, MAT_RIPPLE_GLOBAL_OPTIONS, MatRippleModule, RippleGlobalOptions } from '@angular/material/core';
import { ReactiveFormsModule, FormsModule, Validators } from '@angular/forms';
import { MatTabsModule } from '@angular/material/tabs';
import { MatIconModule } from '@angular/material/icon';
import { HTTP_INTERCEPTORS, HttpClient, provideHttpClient, withInterceptorsFromDi } from '@angular/common/http';
import { NotifyModalComponent } from './modal/notify-modal/notify-modal.component';
import { TranslateLoader, TranslateModule } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { HomeComponent } from './page/home/home.component';
import { ContactComponent } from './page/contact/contact.component';
import { TutorialComponent } from './page/tutorial/tutorial.component';
import { ProductComponent } from './page/product/product.component';
import { CartComponent } from './page/cart/cart.component';
import { PlaceOrderComponent } from './page/place-order/place-order.component';
import { LoginComponent } from './page/login/login.component';
import { RegisterComponent } from './page/register/register.component';
import { NotifyComponent } from './modal/notify/notify.component';
import { CollectionComponent } from './page/collection/collection.component';
import { AboutComponent } from './page/about/about.component';
import { HttpInterceptorService } from './services/http-logger.service';
import { ProfileComponent } from './page/profile/profile.component';
import { OrderComponent } from './page/order/order.component';
import { OrderDetailComponent } from './page/order-detail/order-detail.component';

const globalRippleConfig: RippleGlobalOptions = {
  animation: {
    enterDuration: 400,
    exitDuration: 0
  }
};

export function HttpLoaderFactory(http: HttpClient) {
  return new TranslateHttpLoader(http);
}

@NgModule({
  declarations: [
    AppComponent,
    HeaderComponent,
    MainComponent,
    FooterComponent,
    NotifyModalComponent,
    HomeComponent,
    ContactComponent,
    TutorialComponent,
    ProductComponent,
    CartComponent,
    PlaceOrderComponent,
    LoginComponent,
    RegisterComponent,
    NotifyComponent,
    CollectionComponent,
    AboutComponent,
    ProfileComponent,
    OrderComponent,
    OrderDetailComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    MatSnackBarModule,
    MatTooltipModule,
    MatRippleModule,
    ReactiveFormsModule,
    FormsModule,
    MatTabsModule,
    MatIconModule,
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: HttpLoaderFactory,
        deps: [HttpClient]
      }
    })
  ],
  providers: [
    {
      provide: MAT_RIPPLE_GLOBAL_OPTIONS, useValue: globalRippleConfig
    },
    {
      provide: MAT_DATE_LOCALE, useValue: 'vi-VN'
    },
    {
      provide: HTTP_INTERCEPTORS,
      useClass: HttpInterceptorService,
      multi: true
    },
    provideAnimationsAsync(),
    provideHttpClient(withInterceptorsFromDi())
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
