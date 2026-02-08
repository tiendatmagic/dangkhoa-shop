import { Component, ViewChild, ElementRef } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../../services/auth.service';
import { DataService } from '../../../services/data.service';
import { ApiCacheService } from '../../../services/api-cache.service';
import { CategoryService } from '../../../services/category.service';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

@Component({
  selector: 'app-admin-create-product',
  standalone: false,
  templateUrl: './admin-create-product.component.html',
  styleUrl: './admin-create-product.component.scss'
})
export class AdminCreateProductComponent {
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
  pendingImages: File[] = [];
  pendingImageUrls: string[] = [];

  @ViewChild('fileInput') fileInput!: ElementRef<HTMLInputElement>;

  constructor(
    private fb: FormBuilder,
    private router: Router,
    private auth: AuthService,
    private dataService: DataService,
    private apiCache: ApiCacheService,
    private http: HttpClient,
    public categoryService: CategoryService
  ) {
    this.productName = new FormControl('', [Validators.required, Validators.minLength(1), Validators.maxLength(30)]);
    this.price = new FormControl(0, [Validators.required, Validators.min(0)]);
    this.quantity = new FormControl('', [Validators.min(0)]);
    this.productType = new FormControl('');
    this.category = new FormControl('');
    this.isBestSeller = new FormControl('0', [Validators.required]);
    this.size = new FormControl('', [Validators.required, Validators.minLength(1)]);
    this.description = new FormControl('', [Validators.maxLength(50000)]);

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
    const files = event.target.files;
    if (files) {
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file && file.type.startsWith('image/') && file.size < 2 * 1024 * 1024) {
          this.addImage(file);
        } else {
          this.dataService.showNotify('Error', `File ${file.name} is not valid`, 'error', true, true, false);
        }
      }
    }
    event.target.value = '';
  }

  addImage(file: File) {
    this.pendingImages.push(file);
    const previewUrl = URL.createObjectURL(file);
    this.pendingImageUrls.push(previewUrl);
  }

  removeImage(index: number) {
    if (this.pendingImageUrls[index]) {
      URL.revokeObjectURL(this.pendingImageUrls[index]);
    }
    this.pendingImages.splice(index, 1);
    this.pendingImageUrls.splice(index, 1);
  }

  createProduct() {
    if (this.productForm.valid && this.pendingImages.length > 0 && !this.isUploading) {
      this.isUploading = true;
      const formData = this.productForm.value;
      const sizeArray = formData.size ? formData.size.split(',').map((s: any) => s.trim()).filter((s: any) => s) : [];

      this.uploadAllImages(sizeArray, formData);
    } else {
      if (this.pendingImages.length === 0) {
        this.dataService.showNotify('Error', 'Please select at least one image', 'error', true, true, false);
      } else if (!this.productForm.valid) {
        this.productForm.markAllAsTouched();
        this.dataService.showNotify('Error', 'Please check the form', 'error', true, true, false);
      }
    }
  }

  private uploadAllImages(sizeArray: string[], formData: any) {
    const uploadPromises = this.pendingImages.map((file) => {
      return new Promise<string>((resolve, reject) => {
        this.auth.uploadImage(file).subscribe(
          (res: { url: string }) => {
            resolve(res.url);
          },
          (err) => {
            reject(err);
          }
        );
      });
    });

    Promise.all(uploadPromises)
      .then((imageUrls: string[]) => {
        // Clean up preview URLs
        this.pendingImageUrls.forEach(url => URL.revokeObjectURL(url));
        this.pendingImages = [];
        this.pendingImageUrls = [];
        this.submitPayload(sizeArray, formData, imageUrls);
      })
      .catch((err) => {
        this.dataService.showNotify('Error', 'Failed to upload one or more images', 'error', true, true, false);
        this.isUploading = false;
      });
  }

  private submitPayload(sizeArray: string[], formData: any, imageUrls: string[]) {
    const payload = {
      name: formData.productName,
      price: formData.price,
      category: formData.category,
      product_type: formData.productType,
      quantity: formData.quantity,
      is_best_seller: parseInt(formData.isBestSeller),
      size: sizeArray,
      images: imageUrls,
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