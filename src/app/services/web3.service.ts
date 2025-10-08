import { Injectable, NgZone } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { BehaviorSubject } from 'rxjs';
import Web3 from 'web3';
import { NotifyModalComponent } from '../modal/notify-modal/notify-modal.component';
import EventTicketABI from '../../assets/abi/EventTicketABI.json';
import { HttpClient } from '@angular/common/http';

declare let window: any;

@Injectable({
  providedIn: 'root',
})
export class Web3Service {
  private web3: Web3 | null = null;
  private accountSubject = new BehaviorSubject<string>('');
  private balanceSubject = new BehaviorSubject<string>('0');
  private USDTBalanceSubject = new BehaviorSubject<string>('0');
  private isConnectedSubject = new BehaviorSubject<boolean>(false);
  private chainIdSubject = new BehaviorSubject<any>('');
  private nativeSymbolSubject = new BehaviorSubject<string>('ETH');
  public isLoading$ = new BehaviorSubject<boolean>(false);
  private contract: any;

  // USDT contract address on BSC
  private readonly USDT_CONTRACT_ADDRESS = '0x55d398326f99059fF775485246999027B3197955';

  selectedChainId: any;
  account$ = this.accountSubject.asObservable();
  balance$ = this.balanceSubject.asObservable();
  USDTBalance$ = this.USDTBalanceSubject.asObservable();
  isConnected$ = this.isConnectedSubject.asObservable();
  chainId$ = this.chainIdSubject.asObservable();
  nativeSymbol$ = this.nativeSymbolSubject.asObservable();

  // Supported chains in the app
  public chainConfig: any = {
    '0x38': {
      symbol: 'BNB',
      name: 'Binance Smart Chain',
      logo: '/assets/images/logo/bsc.png',
      rpcUrls: ['https://bsc-dataseed.binance.org/'],
      blockExplorerUrls: ['https://bscscan.com']
    }
  };

  constructor(
    private ngZone: NgZone,
    public dialog: MatDialog,
    private http: HttpClient
  ) {

  }

  async initWeb3() {
    const savedChainId = localStorage.getItem('selectedChainId');
    this.selectedChainId = savedChainId || '0x38';
    await this.setChainInfo(); // Initialize Web3 with RPC

    if (typeof window.ethereum !== 'undefined') {
      this.web3 = new Web3(window.ethereum);
      try {
        const chainId = await window.ethereum.request({ method: 'eth_chainId' });
        if (!this.chainConfig[chainId]) {
          await this.switchNetwork('0x38');
        }

        const accounts = await this.web3.eth.getAccounts();
        if (accounts.length > 0) {
          this.ngZone.run(() => {
            this.setAccount(accounts[0]);
          });
        }
      } catch (error) {
        console.error('Unable to auto-connect wallet:', error);
      }

      window.ethereum.on('accountsChanged', (accounts: string[]) => {
        this.ngZone.run(() => {
          if (accounts.length > 0) {
            this.setAccount(accounts[0]);
          } else {
            this.disconnectWallet();
          }
        });
      });

      window.ethereum.on('chainChanged', async (chainId: string) => {
        const formattedChainId = chainId.toLowerCase();
        if (!this.chainConfig[formattedChainId]) {
          this.showModal(
            'Warning',
            'The network you selected is not supported. Please switch to a supported network.',
            'error',
            true,
            true
          );
          this.disconnectWallet();
          return;
        }

        this.selectedChainId = formattedChainId;
        this.chainIdSubject.next(formattedChainId);
        localStorage.setItem('selectedChainId', formattedChainId);
        await this.setChainInfo();

        const account = this.accountSubject.value;
        if (account) {
          await this.setAccount(account);
        }
      });
    } else {
      console.warn('MetaMask is not installed, using RPC provider.');
    }
  }

  isMobile(): boolean {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  }

  async connectWallet(): Promise<boolean> {
    this.initWeb3();
    if (typeof window.ethereum !== 'undefined') {
      try {
        const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
        const account = accounts[0];
        this.web3 = new Web3(window.ethereum);
        await this.switchNetwork(this.selectedChainId);
        await this.setAccount(account);
        return true;
      } catch (error) {
        console.warn('Connection error:', error);
        return false;
      }
    } else {
      if (this.isMobile()) {
        const dappUrl = window.location.href;
        window.location.href = `https://metamask.app.link/dapp/${dappUrl}`;
        return false;
      }
      this.showModal('Error', 'MetaMask is not installed!', 'error', true, true, true);
      return false;
    }
  }

  private async setAccount(account: string) {
    this.accountSubject.next(account);
    this.isConnectedSubject.next(true);
    await Promise.all([
      this.getBalance(account),
      this.getUSDTBalance(account)
    ]);
  }

  disconnectWallet() {
    this.accountSubject.next('');
    this.balanceSubject.next('0');
    this.USDTBalanceSubject.next('0');
    this.isConnectedSubject.next(false);
  }

  private async getBalance(account: string) {
    if (this.web3 && account) {
      const balanceInWei = await this.web3.eth.getBalance(account);
      const balance = this.web3.utils.fromWei(balanceInWei, 'ether');
      this.balanceSubject.next(balance);
    }
  }

  private async getUSDTBalance(account: string) {
    if (!this.web3 || !account) {
      this.USDTBalanceSubject.next('0');
      return;
    }

    try {
      const usdtAbi = [
        {
          constant: true,
          inputs: [{ name: '_owner', type: 'address' }],
          name: 'balanceOf',
          outputs: [{ name: 'balance', type: 'uint256' }],
          type: 'function'
        }
      ];

      const contract = new this.web3.eth.Contract(usdtAbi, this.USDT_CONTRACT_ADDRESS);
      const balance: string = await contract.methods['balanceOf'](account).call();
      if (!balance) {
        this.USDTBalanceSubject.next('0');
        return;
      }

      const balanceInUSDT = this.web3.utils.fromWei(balance, 'ether');
      this.USDTBalanceSubject.next(balanceInUSDT);
    } catch (error) {
      console.error('Failed to get USDT balance:', error);
      this.USDTBalanceSubject.next('0');
    }
  }

  private async setChainInfo() {
    const chainId = this.selectedChainId;
    const chain = this.chainConfig[chainId] || this.chainConfig['0x38'];
    this.chainIdSubject.next(chainId || '0x38');
    this.nativeSymbolSubject.next(chain.symbol);

    if (!this.web3 || !window.ethereum) {
      this.web3 = new Web3(chain.rpcUrls[0]);
    }

    if (this.web3 && chain.contractAddress && chain.abi) {
      this.contract = new this.web3.eth.Contract(chain.abi, chain.contractAddress);
    }
  }

  async switchNetwork(chainId: string): Promise<void> {
    const formattedChainId = chainId.startsWith('0x') ? chainId.toLowerCase() : '0x' + parseInt(chainId).toString(16);
    if (!this.chainConfig[formattedChainId]) {
      throw new Error(`Chain ID ${formattedChainId} not found in chainConfig`);
    }

    this.selectedChainId = formattedChainId;
    this.chainIdSubject.next(formattedChainId);
    localStorage.setItem('selectedChainId', formattedChainId);

    if (typeof window.ethereum !== 'undefined') {
      try {
        await window.ethereum.request({
          method: 'wallet_switchEthereumChain',
          params: [{ chainId: formattedChainId }],
        });
      } catch (switchError: any) {
        if (switchError.code === 4902) {
          const network = this.chainConfig[formattedChainId];
          const chainParams = {
            chainId: formattedChainId,
            chainName: network.name,
            nativeCurrency: {
              name: 'Ether',
              symbol: network.symbol,
              decimals: 18,
            },
            rpcUrls: network.rpcUrls,
            blockExplorerUrls: network.blockExplorerUrls || [],
          };

          await window.ethereum.request({
            method: 'wallet_addEthereumChain',
            params: [chainParams],
          });

          await window.ethereum.request({
            method: 'wallet_switchEthereumChain',
            params: [{ chainId: formattedChainId }],
          });
        } else {
          throw switchError;
        }
      }
    }

    await this.setChainInfo();

    const account = this.accountSubject.value;
    if (account) {
      await this.setAccount(account);
    }
  }

  async getBalanceFunc(address: string = '') {
    await this.setChainInfo();
    if (!this.contract || !this.web3) {
      return;
    }

    try {
      const balance: any = await this.contract.methods.balanceOf(address).call();
      return balance.toString();
    } catch (error) {
      console.error('Failed to get token balance:', error);
      return 0;
    }
  }

  async checkInFunc(tokenId: number): Promise<string | void> {
    if (this.isLoading$.value) return;
    this.setChainInfo();

    if (!this.contract || !this.web3) {
      this.showModal('Error', 'Contract not initialized or Web3 not available.', 'error');
      return;
    }
    if (!this.accountSubject.value || !tokenId) {
      this.showModal('Error', 'Please connect your wallet first.', 'error');
      return;
    }

    this.isLoading$.next(true);

    try {
      const gasPrice = await this.web3.eth.getGasPrice();

      return new Promise<string>((resolve, reject) => {
        this.contract.methods
          .checkIn(tokenId)
          .send({
            from: this.accountSubject.value,
            gasPrice
          })
          .on('transactionHash', (hash: string) => {
            resolve(hash);
          })
          .once('receipt', (receipt: any) => {
          })
          .on('error', (err: any) => {
            this.showModal('Error', 'Check-in failed. Please try again.', 'error');
            reject(err);
          })
          .finally(() => {
            this.isLoading$.next(false);
            this.setAccount(this.accountSubject.value);
          });
      });
    } catch (error) {
      console.error('Check-in failed:', error);
      this.showModal('Error', 'Check-in failed. Please try again.', 'error');
      this.isLoading$.next(false);
      await this.setAccount(this.accountSubject.value);
    }
  }



  async transferUSDT(
    tokenAddress: string,
    merchantAddress: string,
    amount: number,
    decimals: number
  ): Promise<string> {
    if (!this.web3 || !this.accountSubject.value) {
      return Promise.reject('Wallet not connected');
    }

    const usdtAbi = [
      {
        constant: false,
        inputs: [
          { name: '_to', type: 'address' },
          { name: '_value', type: 'uint256' }
        ],
        name: 'transfer',
        outputs: [{ name: '', type: 'bool' }],
        type: 'function'
      }
    ];

    const contract: any = new this.web3.eth.Contract(usdtAbi, tokenAddress);
    const amountInWei = (BigInt(Math.floor(amount * Math.pow(10, decimals)))).toString();

    return new Promise<string>((resolve, reject) => {
      contract.methods
        .transfer(merchantAddress, amountInWei)
        .send({ from: this.accountSubject.value })
        .on('transactionHash', (hash: string) => {
          resolve(hash);
        })
        .once('transactionHash', (hash: string) => {
          resolve(hash);
        })
        .once('receipt', (receipt: any) => {
        })
        .on('error', (err: any) => {
          reject(err);
        })
        .catch((error: any) => {
          reject(error);
        });
    });
  }


  showModal(
    title: string,
    message: string,
    status: string,
    showCloseBtn: boolean = true,
    disableClose: boolean = true,
    installMetamask: boolean = false
  ) {
    this.dialog.closeAll();
    this.dialog.open(NotifyModalComponent, {
      disableClose: disableClose,
      width: '90%',
      maxWidth: '400px',
      data: {
        title: title,
        message: message,
        status: status,
        showCloseBtn: showCloseBtn,
        installMetamask: installMetamask,
      },
    });
  }
}