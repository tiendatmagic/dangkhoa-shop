import { Component } from '@angular/core';
import { Web3Service } from '../../services/web3.service';
import { DataService } from '../../services/data.service';

@Component({
  selector: 'app-collection',
  standalone: false,
  templateUrl: './collection.component.html',
  styleUrl: './collection.component.scss'
})
export class CollectionComponent {
  allProducts: any[] = [];
  productList: any[] = [];
  filterArray: string[] = [];
  selectedPrice: number = 0;

  constructor(private web3Service: Web3Service, private dataService: DataService) { }

  ngOnInit() {
    this.allProducts = this.dataService.productList;
    this.productList = [...this.allProducts];
  }

  get bestSellerProducts() {
    return this.productList.filter(product => product.isBestSeller);
  }

  getProductsByCategory(category: string, event: any) {
    if (event.target.checked) {
      if (!this.filterArray.includes(category)) {
        this.filterArray.push(category);
      }
    } else {
      this.filterArray = this.filterArray.filter(c => c !== category);
    }

    if (this.filterArray.length === 0) {
      this.productList = [...this.allProducts];
    } else {
      this.productList = this.allProducts.filter(product => this.filterArray.includes(product.category));
    }
  }

  onPriceChange(price: number) {
    this.productList = this.allProducts.filter(product => product.price >= price);
  }

  onSortChange(event: any) {
    const value = event.target.value;

    if (value === 'low-high') {
      this.productList.sort((a, b) => a.price - b.price);
    } else if (value === 'high-low') {
      this.productList.sort((a, b) => b.price - a.price);
    } else {
      this.productList.sort((a, b) => a.id - b.id);
    }
  }
}
