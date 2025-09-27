import { Component } from '@angular/core';
import { Web3Service } from '../../services/web3.service';
import { DataService } from '../../services/data.service';

@Component({
  selector: 'app-home',
  standalone: false,
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss'
})
export class HomeComponent {
  productList: any[] = [];

  constructor(private web3Service: Web3Service, private dataService: DataService) { }
  ngOnInit() {
    this.productList = this.dataService.productList;
  }


  get bestSellerProducts() {
    return this.productList.filter(product => product.isBestSeller);
  }


  test() {
    this.web3Service.getBalanceFunc('0x18E215E111aa8877266E9F8CDeDf21f605777777');
  }
}
