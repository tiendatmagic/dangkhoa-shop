import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AdminTabService {
  private active = new BehaviorSubject<string>('product');
  activeTab$ = this.active.asObservable();

  select(tab: string) {
    this.active.next(tab);
  }
}
