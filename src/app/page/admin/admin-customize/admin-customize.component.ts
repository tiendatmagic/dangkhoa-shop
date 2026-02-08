import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CKEditorModule } from '@ckeditor/ckeditor5-angular';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { AuthService } from '../../../services/auth.service';
import { DataService } from '../../../services/data.service';
import { ApiCacheService } from '../../../services/api-cache.service';
import { AdminTabService } from '../../../services/admin-tab.service';
import { CategoryService } from '../../../services/category.service';
import { forkJoin, of, Subject } from 'rxjs';
import { catchError, debounceTime, distinctUntilChanged, switchMap, takeUntil, finalize } from 'rxjs/operators';

@Component({
  selector: 'app-admin-customize',
  standalone: true,
  templateUrl: './admin-customize.component.html',
  styleUrl: './admin-customize.component.scss',
  imports: [CommonModule, FormsModule, CKEditorModule]
})
export class AdminCustomizeComponent implements OnInit, OnDestroy {
  public Editor: any = ClassicEditor;
  public editorConfig: any = {
    toolbar: {
      items: [
        'heading',
        '|',
        'bold', 'italic', 'underline', 'link',
        '|',
        'bulletedList', 'numberedList',
        '|',
        'blockQuote',
        '|',
        'undo', 'redo'
      ]
    }
  };

  slides: string[] = [];
  banner: string = '';
  collections: any[] = [];
  selectedCollections: any[] = [];
  aboutContent: string = '';
  contactContent: string = '';
  // Fixed categories for Shop By Category
  categories: Array<{ id: string; name: string; image?: string; customName?: string }> = [
    { id: 'men', name: 'Men' },
    { id: 'women', name: 'Women' },
    { id: 'crypto', name: 'Crypto' },
    { id: 'gold_silver', name: 'Gold/Silver' },
    { id: 'other', name: 'Other' },
  ];
  editingCategoryId: string | null = null;
  tempCategoryName: string = '';
  uploading = false;
  saving = false;
  lastSavedAt: number | null = null;
  private _savedTimer: any = null;
  private destroy$ = new Subject<void>();
  isLoading = new Map<string, boolean>();
  private customizationSub: any = null;

  constructor(private auth: AuthService, private data: DataService, private apiCache: ApiCacheService, private adminTab: AdminTabService, private categoryService: CategoryService) { }

  ngOnInit(): void {
    // Setup cancellable, debounced tab-driven requests from AdminTabService
    this.adminTab.activeTab$.pipe(
      distinctUntilChanged(),
      debounceTime(150),
      switchMap(tab => {
        if (tab === 'customize') {
          this.isLoading.set('customize', true);
          return forkJoin({
            customization: this.apiCache.getCached('customization', this.auth.getCustomization().pipe(catchError(() => of({ slides: [], collections: [] })))),
            collections: this.apiCache.getCached('collections', this.auth.getCollections().pipe(catchError(() => of([]))))
          });
        }
        return of(null);
      }),
      takeUntil(this.destroy$)
    ).subscribe((res: any) => {
      if (!res) return;
      const { customization, collections } = res;
      if (customization) {
        this.slides = customization.slides || [];
        this.selectedCollections = customization.collections || [];
        // load banner if provided (supports keys `banner` or `homeBanner`)
        this.banner = customization.banner || customization.homeBanner || '';
        this.aboutContent = customization.about_content || '';
        this.contactContent = customization.contact_content || '';
        if (Array.isArray(customization.collections)) {
          customization.collections.forEach((c: any) => {
            const cat = this.categories.find(x => x.id === (c.id || String(c.name).toLowerCase().replace(/[^a-z0-9]+/g, '_')));
            if (cat) {
              if (c.image) {
                cat.image = c.image;
              }
              if (c.customName) {
                cat.customName = c.customName;
              }
            }
          });
        }
      }
      if (collections) {
        this.collections = collections || [];
      }
      this.isLoading.set('customize', false);
    }, () => {
      this.isLoading.set('customize', false);
    });
  }

  loadCustomization() {
    // trigger a refresh of customization (cancels previous pending)
    this.fetchCustomization();
  }

  loadCollections() {
    // refresh customization which also loads collections
    this.fetchCustomization();
  }

  onFileChange(event: any) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;
    this.uploading = true;
    this.auth.uploadImage(file).pipe(takeUntil(this.destroy$), finalize(() => { this.uploading = false; })).subscribe((r: any) => {
      if (r && r.url) {
        this.slides.push(r.url);
        this.save();
      }
    }, (err) => {
      console.error('Slide upload error', err);
    });
  }

  onCategoryFileChange(event: any, category: any) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;
    this.uploading = true;
    this.auth.uploadImage(file).pipe(takeUntil(this.destroy$), finalize(() => { this.uploading = false; })).subscribe((r: any) => {
      if (r && r.url) {
        category.image = r.url;
        this.save();
      }
    }, (err) => {
      console.error('Category upload error', err);
    });
  }

  onBannerFileChange(event: any) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;
    this.uploading = true;
    this.auth.uploadImage(file).pipe(takeUntil(this.destroy$), finalize(() => { this.uploading = false; })).subscribe((r: any) => {
      if (r && r.url) {
        this.banner = r.url;
        this.save();
      }
    }, (err) => {
      console.error('Banner upload error', err);
    });
  }

  removeSlide(index: number) {
    this.slides.splice(index, 1);
  }

  moveSlideUp(index: number) {
    if (index <= 0) return;
    const tmp = this.slides[index - 1];
    this.slides[index - 1] = this.slides[index];
    this.slides[index] = tmp;
  }

  moveSlideDown(index: number) {
    if (index >= this.slides.length - 1) return;
    const tmp = this.slides[index + 1];
    this.slides[index + 1] = this.slides[index];
    this.slides[index] = tmp;
  }

  toggleCollection(col: any) {
    const found = this.selectedCollections.find((c: any) => c.id === col.id);
    if (found) {
      this.selectedCollections = this.selectedCollections.filter((c: any) => c.id !== col.id);
    } else {
      this.selectedCollections.push({ id: col.id, name: col.name, image: col.image });
    }
  }

  isSelected(col: any): boolean {
    return !!this.selectedCollections && this.selectedCollections.some((c: any) => c.id === col.id);
  }

  toFullUrl(path: string | undefined): string {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    // Ensure no duplicate slashes
    const base = this.auth.getBaseUrl().replace(/\/$/, '');
    return base + path;
  }

  save() {
    if (this.saving) return;
    this.saving = true;
    // include selected collections and any category images (Shop By Category)
    const collectionsPayload: any[] = this.selectedCollections.map(c => {
      const payload: any = { id: c.id, name: c.name };
      // Add image and customName from the fixed categories if they exist
      const cat = this.categories.find(x => x.id === c.id);
      if (cat) {
        if (cat.image) payload.image = cat.image;
        if (cat.customName) payload.customName = cat.customName;
      }
      if (c.image) payload.image = c.image;
      return payload;
    });

    const payload = {
      slides: this.slides,
      collections: collectionsPayload,
      banner: this.banner,
      about_content: this.aboutContent,
      contact_content: this.contactContent,
    };
    this.auth.saveCustomization(payload).subscribe(() => {
      this.saving = false;
      this.data.showNotify('Saved', 'Customization saved successfully', 'success');
      this.lastSavedAt = Date.now();
      if (this._savedTimer) clearTimeout(this._savedTimer);
      this._savedTimer = setTimeout(() => this.lastSavedAt = null, 3000);
      // update cache so public endpoint is fresh
      this.apiCache.put('customization', { slides: payload.slides, collections: payload.collections, banner: payload.banner, about_content: payload.about_content, contact_content: payload.contact_content });
      // ensure home uses the newest public customization key
      try { this.apiCache.invalidate('public_customization'); } catch { }
      // refresh category names in the service
      this.categoryService.refreshCategoryNames();
    }, (err) => {
      this.saving = false;
      console.error('Save customization error', err);
      const msg = err?.error?.error || err?.error?.message || JSON.stringify(err?.error) || 'Failed to save customization';
      this.data.showNotify('Error', msg, 'error');
    });
  }

  selectTab(tab: string) {
    this.adminTab.select(tab);
  }

  fetchCustomization() {
    if (this.customizationSub) {
      try { this.customizationSub.unsubscribe(); } catch { }
      this.customizationSub = null;
    }
    this.isLoading.set('customize', true);
    this.customizationSub = forkJoin({
      customization: this.apiCache.getCached('customization', this.auth.getCustomization().pipe(catchError(() => of({ slides: [], collections: [] })))),
      collections: this.apiCache.getCached('collections', this.auth.getCollections().pipe(catchError(() => of([]))))
    }).pipe(takeUntil(this.destroy$)).subscribe((res: any) => {
      if (!res) return;
      const { customization, collections } = res;
      if (customization) {
        this.slides = customization.slides || [];
        this.selectedCollections = customization.collections || [];
        // load banner if provided
        this.banner = customization.banner || customization.homeBanner || '';
        this.aboutContent = customization.about_content || '';
        this.contactContent = customization.contact_content || '';
        if (Array.isArray(customization.collections)) {
          customization.collections.forEach((c: any) => {
            const cat = this.categories.find(x => x.id === (c.id || String(c.name).toLowerCase().replace(/[^a-z0-9]+/g, '_')));
            if (cat) {
              if (c.image) {
                cat.image = c.image;
              }
              if (c.customName) {
                cat.customName = c.customName;
              }
            }
          });
        }
      }
      if (collections) {
        this.collections = collections || [];
      }
      this.isLoading.set('customize', false);
    }, () => {
      this.isLoading.set('customize', false);
    });
  }

  startEditCategoryName(cat: any) {
    this.editingCategoryId = cat.id;
    this.tempCategoryName = cat.customName || cat.name;
  }

  cancelEditCategoryName() {
    this.editingCategoryId = null;
    this.tempCategoryName = '';
  }

  saveCategoryName(cat: any) {
    if (this.tempCategoryName.trim()) {
      cat.customName = this.tempCategoryName.trim();
      this.editingCategoryId = null;
      this.tempCategoryName = '';
      this.save();
    }
  }

  getCategoryDisplayName(cat: any): string {
    return cat.customName || cat.name;
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }
}
