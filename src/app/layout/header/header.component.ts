import { Component, OnInit } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Web3Service } from '../../services/web3.service';
import { initFlowbite } from 'flowbite';
import { combineLatest } from 'rxjs';
import { TranslateService } from '@ngx-translate/core';
import { DataService } from '../../services/data.service';
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-header',
  standalone: false,
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss'],
})
export class HeaderComponent implements OnInit {
  account: string = '';
  balance: any;
  nativeSymbol: string = '';
  isConnected: boolean = false;
  selectedNetwork: string = '0xa4b1';
  selectedNetworkImg: string = '';
  selectedNetworkName: string = 'Arbitrum One';
  // dropdownOpen: boolean = false;
  networks: any;
  lang: string = 'vi';
  isLogin: boolean = false;
  cartCount: number = 0;

  constructor(public web3Service: Web3Service, private router: Router, private snackBar: MatSnackBar, public translate: TranslateService, private dataService: DataService, private auth: AuthService) {
    this.web3Service.chainId$.subscribe((networkId: any) => {
      this.selectedNetwork = networkId;
      this.selectedNetworkImg = this.web3Service.chainConfig[this.selectedNetwork]?.logo || '';
      this.selectedNetworkName = this.web3Service.chainConfig[this.selectedNetwork]?.name || 'Unknown Network';
    });
    this.dataService.cartCount$.subscribe((count: number) => {
      this.cartCount = count;
    });
    this.auth.isLogin$.subscribe((value) => {
      this.isLogin = value;
    });
  }

  ngOnInit(): void {
    if (localStorage.getItem('dat-shop-token')) {
      this.isLogin = true;
    }

    if (this.translate.currentLang == 'vi') {
      this.lang = 'vi';
    }
    else {
      this.lang = 'en';
    }
    // Lấy tất cả các chainId từ chainConfig
    this.networks = Object.keys(this.web3Service.chainConfig);
    this.selectedNetwork = this.web3Service.selectedChainId || this.networks[0];
    this.selectedNetworkName = this.web3Service.chainConfig[this.selectedNetwork]?.name || 'Unknown Network';

    // Gộp các observable vào một để theo dõi đồng thời
    combineLatest([
      this.web3Service.account$,
      this.web3Service.balance$,
      this.web3Service.nativeSymbol$,
      this.web3Service.isConnected$,
      this.web3Service.chainId$
    ]).subscribe(([account, balance, nativeSymbol, isConnected, chainId]) => {
      this.account = account;
      this.balance = balance;
      this.nativeSymbol = nativeSymbol;
      this.isConnected = isConnected;
      this.selectedNetwork = chainId;
    });

    initFlowbite();
  }

  connectWallet() {
    this.web3Service.connectWallet();
  }

  disconnectWallet() {
    this.web3Service.disconnectWallet();
  }

  onLogout() {
    this.auth.onLogout().subscribe((res: any) => {
      localStorage.removeItem("dat-shop-renew");
      localStorage.removeItem("dat-shop-token");
      localStorage.removeItem("dat-shop-profile");
      this.auth.getToken = '';
      this.auth.isLogin = false;
      this.auth.onLoad = true;
      this.auth.isLogin = false;
      this.auth.getProfile = null;

    },
      (error: any) => {
        this.auth.getToken = '';
        this.auth.isLogin = false;
        localStorage.removeItem("dat-shop-renew");
        localStorage.removeItem("dat-shop-token");
        localStorage.removeItem("dat-shop-profile");

        this.router.navigate(['/login']);

      }
    );
  }

  onNetworkChange(event: Event) {
    const selectElement = event.target as HTMLSelectElement;
    this.selectedNetwork = selectElement.value;

    // Gọi phương thức switchNetwork từ Web3Service
    this.web3Service.switchNetwork(this.selectedNetwork)
      .then(() => {
        console.log(`Switched to network: ${this.selectedNetwork}`);
      })
      .catch((error) => {
        console.error('Error switching network:', error);
      });
  }


  chooseNetwork(networkId: string) {
    this.web3Service.switchNetwork(networkId);
  }

  openUserMenu() {
    initFlowbite();
  }

  copyAddress(address: string): void {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(address).then(() => {
        console.log('Address copied to clipboard');
        this.snackBar.open('Address copied to clipboard', 'OK', {
          horizontalPosition: 'right',
          verticalPosition: 'bottom',
          duration: 3000
        });
      }).catch((error) => {
        console.error('Failed to copy address: ', error);
      });
    } else {
      let textArea = document.createElement("textarea");
      textArea.value = address;
      document.body.appendChild(textArea);
      textArea.select();
      try {
        document.execCommand('copy');
      } catch (error) {
        console.error('Failed to copy address: ', error);
      }
      document.body.removeChild(textArea);
    }
  }

  goHome() {
    initFlowbite();
  }
}
