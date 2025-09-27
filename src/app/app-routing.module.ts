import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './page/home/home.component';
import { ContactComponent } from './page/contact/contact.component';
import { TutorialComponent } from './page/tutorial/tutorial.component';
import { ProductComponent } from './page/product/product.component';
import { CartComponent } from './page/cart/cart.component';
import { PlaceOrderComponent } from './page/place-order/place-order.component';
import { LoginComponent } from './page/login/login.component';
import { RegisterComponent } from './page/register/register.component';
import { CollectionComponent } from './page/collection/collection.component';
import { AboutComponent } from './page/about/about.component';
import { ProfileComponent } from './page/profile/profile.component';
import { OrderComponent } from './page/order/order.component';
import { OrderDetailComponent } from './page/order-detail/order-detail.component';

const routes: Routes = [
  {
    path: '',
    component: HomeComponent,
  },
  {
    path: 'home',
    component: HomeComponent,
  },
  {
    path: 'collection',
    component: CollectionComponent,
  },
  {
    path: 'about',
    component: AboutComponent,
  },
  {
    path: 'contact',
    component: ContactComponent,
  },
  {
    path: 'profile',
    component: ProfileComponent,
  },
  {
    path: 'tutorial',
    component: TutorialComponent,
  },
  {
    path: 'cart',
    component: CartComponent,
  },
  {
    path: 'checkout',
    component: PlaceOrderComponent,
  },
  {
    path: 'order',
    component: OrderComponent,
  },
  {
    path: 'order-detail/:id',
    component: OrderDetailComponent,
  },
  {
    path: 'login',
    component: LoginComponent,
  },
  {
    path: 'register',
    component: RegisterComponent,
  },
  {
    path: 'checkout',
    component: PlaceOrderComponent,
  },
  {
    path: 'checkout/:id',
    component: PlaceOrderComponent,
  },
  {
    path: 'product/:id',
    component: ProductComponent,
  },
  { path: '**', redirectTo: '', pathMatch: 'full' },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
