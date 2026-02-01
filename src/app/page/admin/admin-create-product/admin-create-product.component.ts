import { Component, ViewChild, ElementRef } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../../services/auth.service';
import { DataService } from '../../../services/data.service';
import { ApiCacheService } from '../../../services/api-cache.service';

@Component({
  selector: 'app-admin-create-product',
  standalone: false,
  templateUrl: './admin-create-product.component.html',
  styleUrl: './admin-create-product.component.scss'
})
export class AdminCreateProductComponent {
  productName: FormControl;
  price: FormControl;
  quantity: FormControl;
  productType: FormControl;
  category: FormControl;
  isBestSeller: FormControl;
  size: FormControl;
  description: FormControl;
  productForm: FormGroup;
  isLoading: boolean = false;
  isUploading: boolean = false;
  previewUrl: string | null = null;
  pendingImage: File | null = null;

  @ViewChild('fileInput') fileInput!: ElementRef<HTMLInputElement>;

  constructor(
    private fb: FormBuilder,
    private router: Router,
    private auth: AuthService,
    private dataService: DataService,
    private apiCache: ApiCacheService,
    private http: HttpClient
  ) {
    this.productName = new FormControl('', [Validators.required, Validators.minLength(1), Validators.maxLength(30)]);
    this.price = new FormControl(0, [Validators.required, Validators.min(0)]);
    this.quantity = new FormControl('', [Validators.min(0)]);
    this.productType = new FormControl('');
    this.category = new FormControl('');
    this.isBestSeller = new FormControl('0', [Validators.required]);
    this.size = new FormControl('', [Validators.required, Validators.minLength(1)]);
    this.description = new FormControl('', [Validators.maxLength(1000)]);

    this.productForm = fb.group({
      productName: this.productName,
      price: this.price,
      quantity: this.quantity,
      productType: this.productType,
      category: this.category,
      isBestSeller: this.isBestSeller,
      size: this.size,
      description: this.description,
    });
  }


  onImageClick() {
    this.fileInput.nativeElement.click();
  }

  onFileSelected(event: any) {
    const file = event.target.files[0];
    if (file && file.type.startsWith('image/') && file.size < 2 * 1024 * 1024) {
      this.pendingImage = file;
      if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
      this.previewUrl = URL.createObjectURL(file);
      event.target.value = '';
    } else {
      this.dataService.showNotify('Error', 'The file is not valid', 'error', true, true, false);
      event.target.value = '';
    }
  }

  createProduct() {
    if (this.productForm.valid && this.pendingImage && !this.isUploading) {
      this.isUploading = true;
      const formData = this.productForm.value;
      const sizeArray = formData.size ? formData.size.split(',').map((s: any) => s.trim()).filter((s: any) => s) : [];

      this.auth.uploadImage(this.pendingImage).subscribe(
        (res: { url: string }) => {
          if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
          this.pendingImage = null;
          this.submitPayload(sizeArray, formData, res.url);
        },
        (err) => {
          this.dataService.showNotify('Error', 'Failed to upload image', 'error', true, true, false);
          this.isUploading = false;
        }
      );
    } else {
      if (!this.pendingImage) {
        this.dataService.showNotify('Error', 'Please select an image', 'error', true, true, false);
      } else if (!this.productForm.valid) {
        this.productForm.markAllAsTouched();
        this.dataService.showNotify('Error', 'Please check the form', 'error', true, true, false);
      }
    }
  }

  private submitPayload(sizeArray: string[], formData: any, imageUrl: string) {
    const payload = {
      name: formData.productName,
      price: formData.price,
      category: formData.category,
      product_type: formData.productType,
      quantity: formData.quantity,
      is_best_seller: parseInt(formData.isBestSeller),
      size: sizeArray,
      image: imageUrl,
      description: formData.description || ''
    };

    this.auth.createProduct(payload).subscribe(
      (res: any) => {
        this.isUploading = false;
        try { this.apiCache.invalidate('admin_products_page1'); } catch { }
        this.dataService.showNotify('Success', 'Create product successfully', 'success', true, true, false);
        this.router.navigate(['/admin/']);
      },
      (err: any) => {
        this.dataService.showNotify('Error', 'Failed to create product', 'error', true, true, false);
        this.isUploading = false;
      }
    );
  }
}