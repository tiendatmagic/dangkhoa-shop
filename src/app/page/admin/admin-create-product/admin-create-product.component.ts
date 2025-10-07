import { Component, ViewChild, ElementRef } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../../services/auth.service';
import { DataService } from '../../../services/data.service';

@Component({
  selector: 'app-admin-create-product',
  standalone: false,
  templateUrl: './admin-create-product.component.html',
  styleUrl: './admin-create-product.component.scss'
})
export class AdminCreateProductComponent {
  productName: FormControl;
  price: FormControl;
  category: FormControl;
  isBestSeller: FormControl;
  size: FormControl;
  profileForm: FormGroup;
  isLoading: boolean = false;
  isUploading: boolean = false;
  previewUrl: string | null = null;
  pendingImage: File | null = null;

  @ViewChild('fileInput') fileInput!: ElementRef<HTMLInputElement>;

  constructor(private fb: FormBuilder, private router: Router, private auth: AuthService, private dataService: DataService) {
    this.productName = new FormControl('', [Validators.required, Validators.minLength(1), Validators.maxLength(30)]);
    this.price = new FormControl('', [Validators.required, Validators.min(0)]);
    this.category = new FormControl('', [Validators.required]);
    this.isBestSeller = new FormControl('0', [Validators.required]);
    this.size = new FormControl('', [Validators.required, Validators.minLength(1)]);

    this.profileForm = fb.group({
      productName: this.productName,
      price: this.price,
      category: this.category,
      isBestSeller: this.isBestSeller,
      size: this.size,
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
    if (this.profileForm.valid && this.pendingImage && !this.isUploading) {
      this.isUploading = true;
      const formData = this.profileForm.value;
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
      // Error messages cụ thể
      if (!this.pendingImage) {
        this.dataService.showNotify('Error', 'Please select an image', 'error', true, true, false);
      } else if (!this.profileForm.valid) {
        this.profileForm.markAllAsTouched();
        this.dataService.showNotify('Error', 'Please check the form', 'error', true, true, false);
      }
    }
  }

  private submitPayload(sizeArray: string[], formData: any, imageUrl: string) {
    const payload = {
      name: formData.productName,
      price: formData.price,
      category: formData.category,
      is_best_seller: parseInt(formData.isBestSeller),
      size: sizeArray,
      image: imageUrl
    };

    this.auth.createProduct(payload).subscribe(
      (res: any) => {
        this.isUploading = false;
        this.dataService.showNotify('Success', 'Create product successfully', 'success', true, true, false);
        this.router.navigate(['/admin/products']);
      },
      (err: any) => {
        this.dataService.showNotify('Error', 'Failed to create product', 'error', true, true, false);
        this.isUploading = false;
      }
    );
  }
}