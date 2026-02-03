import { Component, OnInit, AfterViewInit, OnDestroy, ElementRef, ViewChild, Renderer2 } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Web3Service } from '../../services/web3.service';
import { initFlowbite } from 'flowbite';
import { combineLatest } from 'rxjs';
import { TranslateService } from '@ngx-translate/core';
import { DataService } from '../../services/data.service';
import { AuthService } from '../../services/auth.service';
import { Router, NavigationEnd } from '@angular/router';

@Component({
  selector: 'app-header',
  standalone: false,
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss'],
})
export class HeaderComponent implements OnInit, AfterViewInit, OnDestroy {
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
  isAdmin: number = 0;
  homeActive: boolean = false;
  searchOpen: boolean = false;
  searchTerm: string = '';

  @ViewChild('searchWrapper') searchWrapper!: ElementRef;
  private documentClickListener: (() => void) | null = null;

  constructor(public web3Service: Web3Service, private router: Router, private snackBar: MatSnackBar, public translate: TranslateService, private dataService: DataService, private auth: AuthService, private renderer: Renderer2) {
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
    this.auth.isAdmin$.subscribe((value) => {
      this.isAdmin = Number(value);
    });

    this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        const url = (event as NavigationEnd).urlAfterRedirects || (event as NavigationEnd).url;
        this.homeActive = url === '/' || url.startsWith('/home');
        const urlTree = this.router.parseUrl(url);
        const q = urlTree.queryParams['q'];
        this.searchTerm = typeof q === 'string' ? q : '';
      }
    });
  }

  ngOnInit(): void {
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

  ngAfterViewInit(): void {
    this.documentClickListener = this.renderer.listen('document', 'click', (event: Event) => {
      const target = event.target as Node;
      if (this.searchOpen && this.searchWrapper && !this.searchWrapper.nativeElement.contains(target)) {
        this.searchOpen = false;
      }
    });
  }

  ngOnDestroy(): void {
    if (this.documentClickListener) {
      this.documentClickListener();
      this.documentClickListener = null;
    }
  }

  connectWallet() {
    this.web3Service.connectWallet();
  }

  disconnectWallet() {
    this.web3Service.disconnectWallet();
  }

  onLogout() {
    this.auth.onLogout().subscribe((res: any) => {
      localStorage.removeItem("dangkhoa-profile");
      this.auth.getToken = '';
      this.auth.isLogin = false;
      this.auth.onLoad = true;
      this.auth.isLogin = false;
      this.auth.getProfile = null;
      this.auth.isAdmin = 0;

    },
      (error: any) => {
        this.auth.getToken = '';
        this.auth.isLogin = false;
        this.auth.isAdmin = 0;
        localStorage.removeItem("dangkhoa-profile");
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

  toggleSearch() {
    this.searchOpen = !this.searchOpen;
  }

  onSearchSubmit() {
    const term = this.searchTerm.trim();
    if (!term) {
      this.searchOpen = false;
      return;
    }
    this.router.navigate(['/collection'], {
      queryParams: { q: term },
      queryParamsHandling: 'merge'
    });
    this.searchOpen = false;
  }

  clearSearch() {
    this.searchTerm = '';
    this.router.navigate(['/collection'], {
      queryParams: { q: null },
      queryParamsHandling: 'merge'
    });
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
