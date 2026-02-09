import { HttpClient } from '@angular/common/http';
import { Component, ViewChild, ElementRef } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../../../services/auth.service';
import { DataService } from '../../../services/data.service';
import { CategoryService } from '../../../services/category.service';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

@Component({
  selector: 'app-admin-product-detail',
  standalone: false,
  templateUrl: './admin-product-detail.component.html',
  styleUrl: './admin-product-detail.component.scss'
})
export class AdminProductDetailComponent {
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
  id: any;
  isLoading: boolean = false;
  isUploading: boolean = false;
  imageUrl: string = 'https://picsum.photos/300/300?random=1';
  previewUrl: string | null = null;
  pendingImage: File | null = null;
  urlLink: string = '';

  // Multiple images support
  productImages: string[] = [];
  pendingImages: File[] = [];
  pendingImageUrls: string[] = [];

  @ViewChild('fileInput') fileInput!: ElementRef<HTMLInputElement>;

  constructor(private route: ActivatedRoute, private fb: FormBuilder, private router: Router, private http: HttpClient, private auth: AuthService, private dataService: DataService, public categoryService: CategoryService) {
    this.productName = new FormControl('', [Validators.required, Validators.minLength(1), Validators.maxLength(60)]);
    this.price = new FormControl('', [Validators.required, Validators.min(0)]);
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

  ngOnInit() {
    this.urlLink = this.auth.getBaseUrl();

    this.id = this.route.snapshot.paramMap.get('id');
    if (this.id) {
      this.isLoading = true;
      this.auth.getProductDetail({ id: this.id }).subscribe(
        (res: any) => {
          this.productForm.patchValue({
            productName: res.name || '',
            price: res.price || '',
            quantity: res.quantity || '',
            productType: res.product_type || '',
            category: res.category || '',
            isBestSeller: res.is_best_seller || 0,
            size: Array.isArray(res.size) ? res.size.join(', ') : res.size || '',
            description: res.description || ''
          });

          // Load images
          if (Array.isArray(res.image)) {
            this.productImages = res.image;
            if (this.productImages.length > 0) {
              this.imageUrl = this.productImages[0];
            }
          } else if (res.image) {
            this.productImages = [res.image];
            this.imageUrl = res.image;
          }

          this.isLoading = false;
        },
        (error: any) => {
          this.isLoading = false;
        }
      );
    } else {
      this.router.navigate(['/order']);
    }
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

  removeExistingImage(index: number) {
    const imageUrl = this.productImages[index];

    this.auth.deleteProductImage({ id: this.id, image_url: imageUrl }).subscribe(
      (res: any) => {
        this.productImages = res.product.image || [];
        if (this.productImages.length > 0) {
          this.imageUrl = this.productImages[0];
        }
        this.dataService.showNotify('Success', 'Image deleted successfully', 'success', true, true, false);
      },
      (error: any) => {
        this.dataService.showNotify('Error', 'Failed to delete image', 'error', true, true, false);
      }
    );
  }

  updateProduct() {
    if (this.productForm.valid && !this.isUploading) {
      this.isUploading = true;
      const formData = this.productForm.value;
      const sizeArray = formData.size ? formData.size.split(',').map((s: any) => s.trim()).filter((s: any) => s) : [];

      if (this.pendingImages.length > 0) {
        this.uploadNewImages(sizeArray, formData);
      } else {
        this.submitPayload(sizeArray, formData);
      }
    } else {
      if (!this.productForm.valid) {
        this.productForm.markAllAsTouched();
        this.dataService.showNotify('Error', 'Please check the form', 'error', true, true, false);
      }
    }
  }

  private uploadNewImages(sizeArray: string[], formData: any) {
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
        // Add new images to existing images
        this.productImages = [...this.productImages, ...imageUrls];

        // Clean up preview URLs
        this.pendingImageUrls.forEach(url => URL.revokeObjectURL(url));
        this.pendingImages = [];
        this.pendingImageUrls = [];

        this.submitPayload(sizeArray, formData);
      })
      .catch((err) => {
        this.dataService.showNotify('Error', 'Failed to upload one or more images', 'error', true, true, false);
        this.isUploading = false;
      });
  }

  private submitPayload(sizeArray: string[], formData: any) {
    const payload = {
      id: this.id,
      name: formData.productName,
      price: formData.price,
      category: formData.category,
      product_type: formData.productType,
      quantity: formData.quantity,
      is_best_seller: parseInt(formData.isBestSeller),
      size: sizeArray,
      images: this.productImages,
      description: formData.description || ''
    };

    this.auth.updateProduct(payload).subscribe(
      (response: any) => {
        this.isUploading = false;
        this.dataService.showNotify('Success', 'Update product successfully', 'success', true, true, false);
        this.router.navigate(['/admin/']);
      },
      (error: any) => {
        this.dataService.showNotify('Error', 'Failed to update product', 'error', true, true, false);
        this.isUploading = false;
      }
    );
  }

  handleImageError(event: any) {
    event.target.src = 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg';
  }
}