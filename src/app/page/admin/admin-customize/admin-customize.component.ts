import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../../services/auth.service';
import { DataService } from '../../../services/data.service';

@Component({
  selector: 'app-admin-customize',
  templateUrl: './admin-customize.component.html',
  styleUrl: './admin-customize.component.scss',
  imports: [CommonModule, FormsModule]
})
export class AdminCustomizeComponent implements OnInit {
  slides: string[] = [];
  collections: any[] = [];
  selectedCollections: any[] = [];
  // Fixed categories for Shop By Category
  categories: Array<{ id: string; name: string; image?: string }> = [
    { id: 'men', name: 'Men' },
    { id: 'women', name: 'Women' },
    { id: 'crypto', name: 'Crypto' },
    { id: 'gold_silver', name: 'Gold/Silver' },
    { id: 'other', name: 'Other' },
  ];
  uploading = false;
  saving = false;
  lastSavedAt: number | null = null;
  private _savedTimer: any = null;

  constructor(private auth: AuthService, private data: DataService) { }

  ngOnInit(): void {
    this.loadCustomization();
    this.loadCollections();
  }

  loadCustomization() {
    this.auth.getCustomization().subscribe((res: any) => {
      if (res) {
        this.slides = res.slides || [];
        this.selectedCollections = res.collections || [];
        // Map saved collections to our categories (if image exists)
        if (Array.isArray(res.collections)) {
          res.collections.forEach((c: any) => {
            const cat = this.categories.find(x => x.id === (c.id || String(c.name).toLowerCase().replace(/[^a-z0-9]+/g, '_')));
            if (cat && c.image) {
              cat.image = c.image;
            }
          });
        }
      }
    }, () => { });
  }

  loadCollections() {
    this.auth.getCollections().subscribe((res: any) => {
      this.collections = res || [];
    }, () => { });
  }

  onFileChange(event: any) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;
    this.uploading = true;
    this.auth.uploadImage(file).subscribe((r: any) => {
      if (r && r.url) {
        this.slides.push(r.url);
        // After successful upload, persist customization immediately
        this.save();
      }
      this.uploading = false;
    }, () => { this.uploading = false; });
  }

  onCategoryFileChange(event: any, category: any) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;
    this.uploading = true;
    this.auth.uploadImage(file).subscribe((r: any) => {
      if (r && r.url) {
        category.image = r.url;
        // persist change immediately
        this.save();
      }
      this.uploading = false;
    }, () => { this.uploading = false; });
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
    const payload = {
      slides: this.slides,
      collections: this.selectedCollections.map(c => ({ id: c.id, name: c.name, image: c.image }))
    };
    this.auth.saveCustomization(payload).subscribe(() => {
      this.saving = false;
      this.data.showNotify('Saved', 'Customization saved successfully', 'success');
      this.lastSavedAt = Date.now();
      if (this._savedTimer) clearTimeout(this._savedTimer);
      this._savedTimer = setTimeout(() => this.lastSavedAt = null, 3000);
    }, (err) => {
      this.saving = false;
      this.data.showNotify('Error', 'Failed to save customization', 'error');
    });
  }
}
