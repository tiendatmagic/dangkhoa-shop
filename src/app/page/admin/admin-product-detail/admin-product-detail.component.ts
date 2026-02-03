import { HttpClient } from '@angular/common/http';
import { Component, ViewChild, ElementRef } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../../../services/auth.service';
import { DataService } from '../../../services/data.service';
import { CategoryService } from '../../../services/category.service';

@Component({
  selector: 'app-admin-product-detail',
  standalone: false,
  templateUrl: './admin-product-detail.component.html',
  styleUrl: './admin-product-detail.component.scss'
})
export class AdminProductDetailComponent {
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

  @ViewChild('fileInput') fileInput!: ElementRef<HTMLInputElement>;

  constructor(private route: ActivatedRoute, private fb: FormBuilder, private router: Router, private http: HttpClient, private auth: AuthService, private dataService: DataService, public categoryService: CategoryService) {
    this.productName = new FormControl('', [Validators.required, Validators.minLength(1), Validators.maxLength(30)]);
    this.price = new FormControl('', [Validators.required, Validators.min(0)]);
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

          if (Array.isArray(res.image) && res.image.length > 0) {
            this.imageUrl = res.image[0];
          } else if (res.image) {
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
    const file = event.target.files[0];
    if (file && file.type.startsWith('image/') && file.size < 2 * 1024 * 1024) {
      this.pendingImage = file;

      if (this.previewUrl) {
        URL.revokeObjectURL(this.previewUrl);
      }
      this.previewUrl = URL.createObjectURL(file);
      event.target.value = '';
    } else {
      this.dataService.showNotify('Error', 'The file is not valid', 'error', true, true, false);
      event.target.value = '';
    }
  }

  updateProduct() {
    if (this.productForm.valid && !this.isUploading) {
      this.isUploading = true;
      const formData = this.productForm.value;
      const sizeArray = formData.size ? formData.size.split(',').map((s: any) => s.trim()).filter((s: any) => s) : [];

      if (this.pendingImage) {
        this.auth.uploadImage(this.pendingImage).subscribe(
          (response: { url: string }) => {
            if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
            this.pendingImage = null;
            this.submitPayload(sizeArray, formData, response.url);
          },
          (error: any) => {
            this.dataService.showNotify('Error', 'Failed to upload image', 'error', true, true, false);
            this.isUploading = false;
          }
        );
      } else {
        this.submitPayload(sizeArray, formData, this.imageUrl);
      }
    } else {
      if (!this.pendingImage && !this.imageUrl) {
        this.dataService.showNotify('Error', 'Please select an image', 'error', true, true, false);
      } else if (!this.productForm.valid) {
        this.productForm.markAllAsTouched();
        this.dataService.showNotify('Error', 'Please check the form', 'error', true, true, false);
      }
    }
  }

  private submitPayload(sizeArray: string[], formData: any, finalImageUrl: string) {
    const payload = {
      id: this.id,
      name: formData.productName,
      price: formData.price,
      category: formData.category,
      product_type: formData.productType,
      quantity: formData.quantity,
      is_best_seller: parseInt(formData.isBestSeller),
      size: sizeArray,
      image: finalImageUrl,
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