import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { AuthService } from './auth.service';
import { ApiCacheService } from './api-cache.service';
import { catchError, map } from 'rxjs/operators';
import { of } from 'rxjs';

export interface CategoryMapping {
  [key: string]: string;
}

@Injectable({
  providedIn: 'root'
})
export class CategoryService {
  private categoryNamesSubject = new BehaviorSubject<CategoryMapping>({
    men: 'Men',
    women: 'Women',
    crypto: 'Crypto',
    gold_silver: 'Gold/Silver',
    'gold/silver': 'Gold/Silver',
    other: 'Other'
  });

  public categoryNames$ = this.categoryNamesSubject.asObservable();

  constructor(
    private auth: AuthService,
    private apiCache: ApiCacheService
  ) {
    this.loadCategoryNames();
  }

  loadCategoryNames(): void {
    this.apiCache.getCached('public_customization', this.auth.getPublicCustomization())
      .pipe(
        map((res: any) => {
          const mapping: CategoryMapping = {
            men: 'Men',
            women: 'Women',
            crypto: 'Crypto',
            gold_silver: 'Gold/Silver',
            'gold/silver': 'Gold/Silver',
            other: 'Other'
          };

          if (res && Array.isArray(res.collections)) {
            res.collections.forEach((col: any) => {
              if (col.customName && col.id) {
                mapping[col.id] = col.customName;
                // Handle alternative formats
                if (col.id === 'gold_silver') {
                  mapping['gold/silver'] = col.customName;
                } else if (col.id === 'gold/silver') {
                  mapping['gold_silver'] = col.customName;
                }
              }
            });
          }

          return mapping;
        }),
        catchError(() => {
          return of({
            men: 'Men',
            women: 'Women',
            crypto: 'Crypto',
            gold_silver: 'Gold/Silver',
            'gold/silver': 'Gold/Silver',
            other: 'Other'
          });
        })
      )
      .subscribe(mapping => {
        this.categoryNamesSubject.next(mapping);
      });
  }

  getCategoryName(categoryId: string): string {
    const names = this.categoryNamesSubject.value;
    return names[categoryId] || names[categoryId.toLowerCase()] || categoryId;
  }

  getCategoryNames(): CategoryMapping {
    return this.categoryNamesSubject.value;
  }

  refreshCategoryNames(): void {
    this.apiCache.invalidate('public_customization');
    this.loadCategoryNames();
  }
}
