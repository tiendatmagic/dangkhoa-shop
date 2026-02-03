import { Component, OnDestroy, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth.service';
import { ApiCacheService } from '../../services/api-cache.service';
import { Subject, of } from 'rxjs';
import { catchError, takeUntil } from 'rxjs/operators';

@Component({
  selector: 'app-contact',
  standalone: false,
  templateUrl: './contact.component.html',
  styleUrl: './contact.component.scss'
})
export class ContactComponent implements OnInit, OnDestroy {
  loading = true;
  contactHtml = '';
  private destroy$ = new Subject<void>();

  private fallbackHtml = `
<div class="flex flex-col items-start gap-12 my-10 md:flex-row">
  <img
    class="rounded-lg w-full md:max-w-[450px]"
    alt=""
    src="https://cdn.prod.website-files.com/637dd8d62ccaf602c8ad331c/642af2c5635cbd3df4920e62_furniture%20(2).webp"
  />
  <div class="flex flex-col justify-center gap-6 leading-6 text-gray-600 md:w-2/4">
    <p class="text-xl font-semibold text-gray-600">Our Store</p>
    <p class="text-gray-500">
      Khanh Hoa <br />
      Vietnam
    </p>
  </div>
</div>
`;

  constructor(private auth: AuthService, private apiCache: ApiCacheService) {}

  ngOnInit(): void {
    this.loading = true;
    this.apiCache.getCached(
      'public_customization',
      this.auth.getPublicCustomization().pipe(catchError(() => of(null)))
    ).pipe(takeUntil(this.destroy$)).subscribe((res: any) => {
      const html = res?.contact_content;
      this.contactHtml = (typeof html === 'string' && html.trim().length > 0) ? html : this.fallbackHtml;
      this.loading = false;
    });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
