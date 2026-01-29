import { Injectable } from '@angular/core';
import { Observable, of, Subject } from 'rxjs';
import { tap, shareReplay } from 'rxjs/operators';

@Injectable({ providedIn: 'root' })
export class ApiCacheService {
  private cache = new Map<string, { ts: number; data: any }>();
  private DEFAULT_TTL = 1 * 60 * 1000; // 1 minute
  private invalidated$ = new Subject<string | null>();
  public cacheInvalidated$ = this.invalidated$.asObservable();

  constructor() {
    // Clear cache on full page unload/reload
    if (typeof window !== 'undefined' && window.addEventListener) {
      window.addEventListener('beforeunload', () => {
        this.invalidateAll();
      });
    }
  }

  getCached(key: string, fetch$: Observable<any>, ttlMs?: number): Observable<any> {
    const ttl = ttlMs ?? this.DEFAULT_TTL;
    const cur = this.cache.get(key);
    if (cur && (Date.now() - cur.ts) < ttl) {
      return of(cur.data);
    }
    return fetch$.pipe(
      tap(data => this.cache.set(key, { ts: Date.now(), data })),
      shareReplay(1)
    );
  }

  put(key: string, data: any, ttlMs?: number) {
    this.cache.set(key, { ts: Date.now(), data });
    try { this.invalidated$.next(key); } catch {}
  }

  invalidate(key: string) {
    this.cache.delete(key);
    try { this.invalidated$.next(key); } catch {}
  }

  invalidateAll() {
    this.cache.clear();
    try { this.invalidated$.next(null); } catch {}
  }
}
