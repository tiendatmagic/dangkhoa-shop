import { HttpClient } from '@angular/common/http';
import { Component, ViewChild, ElementRef } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../../../services/auth.service';
import { DataService } from '../../../services/data.service';

@Component({
  selector: 'app-admin-product-detail',
  standalone: false,
  templateUrl: './admin-product-detail.component.html',
  styleUrl: './admin-product-detail.component.scss'
})
export class AdminProductDetailComponent {
  productName: FormControl;
  price: FormControl;
  category: FormControl;
  isBestSeller: FormControl;
  size: FormControl;
  productForm: FormGroup;
  id: any;
  isLoading: boolean = false;
  imageUrl: string = 'https://picsum.photos/300/300?random=1';
  previewUrl: string | null = null;
  pendingImage: File | null = null;
  urlLink: string = '';


  @ViewChild('fileInput') fileInput!: ElementRef<HTMLInputElement>;

  constructor(private route: ActivatedRoute, _fb: FormBuilder, private router: Router, private http: HttpClient, private auth: AuthService, private dataService: DataService) {
    this.productName = new FormControl('', [Validators.required]);
    this.price = new FormControl('', [Validators.required]);
    this.category = new FormControl('', [Validators.required]);
    this.isBestSeller = new FormControl('', [Validators.required]);
    this.size = new FormControl('', [Validators.required]);
    this.productForm = _fb.group({
      productName: this.productName,
      price: this.price,
      category: this.category,
      isBestSeller: this.isBestSeller,
      size: this.size,
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
            category: res.category || '',
            isBestSeller: res.is_best_seller || 0,
            size: Array.isArray(res.size) ? res.size.join(', ') : res.size || ''
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
      event.target.value = '';
    }
  }

  updateProduct() {
    if (this.productForm.valid) {
      const formData = this.productForm.value;
      const sizeArray = formData.size ? formData.size.split(',').map((s: any) => s.trim()) : [];

      if (this.pendingImage) {
        this.auth.uploadImage(this.pendingImage).subscribe(
          (response: { url: string }) => {
            if (this.previewUrl) {
              URL.revokeObjectURL(this.previewUrl);
              this.previewUrl = null;
            }
            this.pendingImage = null;

            this.submitPayload(sizeArray, formData, response.url);
          },
          (error: any) => {
          }
        );
      } else {
        this.submitPayload(sizeArray, formData, this.imageUrl);
      }
    } else {
      this.productForm.markAllAsTouched();
    }
  }

  private submitPayload(sizeArray: string[], formData: any, finalImageUrl: string) {
    const payload = {
      id: this.id,
      name: formData.productName,
      price: formData.price,
      category: formData.category,
      is_best_seller: parseInt(formData.isBestSeller),
      size: sizeArray,
      image: finalImageUrl
    };

    this.auth.updateProduct(payload).subscribe(
      (response: any) => {
        this.router.navigate(['/admin']);
      },
      (error: any) => {
      }
    );
  }
}