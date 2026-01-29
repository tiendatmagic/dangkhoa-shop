import { Component, OnInit, OnDestroy } from '@angular/core';
import { AuthService } from '../../../services/auth.service';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminTabService } from '../../../services/admin-tab.service';
import { ApiCacheService } from '../../../services/api-cache.service';
import { Subject, forkJoin, of } from 'rxjs';
import { catchError, debounceTime, distinctUntilChanged, switchMap, takeUntil } from 'rxjs/operators';
import {
  ApexAxisChartSeries,
  ApexChart,
  ApexXAxis,
  ApexStroke,
  ApexDataLabels,
  ApexYAxis,
  ApexFill,
  ApexGrid,
  ApexLegend,
  ApexTooltip
} from 'ng-apexcharts';

@Component({
  selector: 'app-admin-overview',
  standalone: false,
  templateUrl: './admin-overview.component.html',
  styleUrls: ['./admin-overview.component.scss']
})
export class AdminOverviewComponent implements OnInit, OnDestroy {
  chartOptions: {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    stroke: ApexStroke;
    fill: ApexFill;
    dataLabels: ApexDataLabels;
    grid: ApexGrid;
    legend: ApexLegend;
    yaxis: ApexYAxis;
    colors: string[];
    tooltip: ApexTooltip;
  };



  fromAddress: string = '';
  privateKey: string = '';
  hasPrivateKey: boolean = false;
  savingWallet: boolean = false;

  chainId: number | null = 56;
  rpcUrl: string = '';
  contractAddress: string = '';

  tokenAssets: any[] = [];
  tokenSymbol: string = '';
  tokenIsNative: boolean = false;
  tokenAddress: string = '';
  tokenDecimals: number | null = 18;
  tokenEnabled: boolean = true;
  savingToken: boolean = false;

  ngOnInit() {
    this.adminTab.activeTab$.pipe(
      distinctUntilChanged(),
      debounceTime(150),
      switchMap(tab => {
        if (tab === 'overview') {
          return forkJoin({
            overview: this.apiCache.getCached('admin_overview_chart', this.auth.getOverview().pipe(catchError(() => of({ chart: null })))),
            wallet: this.apiCache.getCached('admin_wallet_settings', this.auth.getWalletSettings().pipe(catchError(() => of({})))),
            tokens: this.apiCache.getCached('admin_token_assets', this.auth.getTokenAssets().pipe(catchError(() => of({ data: [] }))))
          });
        }
        return of(null);
      }),
      takeUntil(this.destroy$)
    ).subscribe((res: any) => {
      if (!res) return;
      const { overview, wallet, tokens } = res;
      if (overview && overview.chart) {
        this.chartOptions = {
          ...this.chartOptions,
          xaxis: { ...this.chartOptions.xaxis, categories: [...overview.chart.categories] },
          series: [...overview.chart.series]
        };
      }
      if (wallet) {
        this.fromAddress = wallet?.from_address || '';
        this.hasPrivateKey = !!wallet?.has_private_key;
        this.chainId = typeof wallet?.chain_id === 'number' ? wallet.chain_id : (wallet?.chain_id ? Number(wallet.chain_id) : 56);
        this.rpcUrl = wallet?.rpc_url || '';
        this.contractAddress = wallet?.contract_address || '';
      }
      if (tokens) {
        this.tokenAssets = tokens?.data || [];
      }
    }, (err) => {
      console.error('Overview load error', err);
    });
  }

  loadChartData() {
    this.fetchOverviewData();
  }

  loadWalletSettings() {
    this.fetchWalletSettings();
  }

  loadTokenAssets() {
    this.fetchTokenAssets();
  }

  private destroy$ = new Subject<void>();

  constructor(private auth: AuthService, private snackBar: MatSnackBar, private adminTab: AdminTabService, private apiCache: ApiCacheService) {
    this.chartOptions = {
      series: [],
      chart: { type: 'area', height: 350 },
      colors: ['#7c3aed'],
      stroke: { curve: 'smooth', width: 2 },
      fill: { opacity: 0.3, colors: ['#7c3aed'] },
      dataLabels: { enabled: true, formatter: (val: number) => val.toLocaleString() },
      grid: { show: true },
      legend: { show: false },
      xaxis: { categories: [], labels: { style: { colors: '#000' } } },
      yaxis: { labels: { style: { colors: '#000' }, formatter: (val: number) => val.toLocaleString() } },
      tooltip: { enabled: false },
    };
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private overviewSub: any = null;
  private walletSub: any = null;
  private tokensSub: any = null;

  fetchOverviewData() {
    // cancel previous combined overview request
    if (this.overviewSub) {
      try { this.overviewSub.unsubscribe(); } catch { }
      this.overviewSub = null;
    }
    // use cached calls where appropriate
    const o$ = this.apiCache.getCached('admin_overview_chart', this.auth.getOverview().pipe(catchError(() => of({ chart: null }))));
    const w$ = this.apiCache.getCached('admin_wallet_settings', this.auth.getWalletSettings().pipe(catchError(() => of({}))));
    const t$ = this.apiCache.getCached('admin_token_assets', this.auth.getTokenAssets().pipe(catchError(() => of({ data: [] }))));

    this.overviewSub = forkJoin({ overview: o$, wallet: w$, tokens: t$ }).pipe(takeUntil(this.destroy$)).subscribe((res: any) => {
      if (!res) return;
      const { overview, wallet, tokens } = res;
      if (overview && overview.chart) {
        this.chartOptions = {
          ...this.chartOptions,
          xaxis: { ...this.chartOptions.xaxis, categories: [...overview.chart.categories] },
          series: [...overview.chart.series]
        };
      }
      if (wallet) {
        this.fromAddress = wallet?.from_address || '';
        this.hasPrivateKey = !!wallet?.has_private_key;
        this.chainId = typeof wallet?.chain_id === 'number' ? wallet.chain_id : (wallet?.chain_id ? Number(wallet.chain_id) : 56);
        this.rpcUrl = wallet?.rpc_url || '';
        this.contractAddress = wallet?.contract_address || '';
      }
      if (tokens) {
        this.tokenAssets = tokens?.data || [];
      }
    }, (err) => {
      console.error('Overview load error', err);
    });
  }

  fetchWalletSettings() {
    if (this.walletSub) {
      try { this.walletSub.unsubscribe(); } catch { }
      this.walletSub = null;
    }
    this.walletSub = this.auth.getWalletSettings().pipe(takeUntil(this.destroy$)).subscribe((res: any) => {
      this.fromAddress = res?.from_address || '';
      this.hasPrivateKey = !!res?.has_private_key;
      this.chainId = typeof res?.chain_id === 'number' ? res.chain_id : (res?.chain_id ? Number(res.chain_id) : 56);
      this.rpcUrl = res?.rpc_url || '';
      this.contractAddress = res?.contract_address || '';
    }, (err) => console.error('Failed to load wallet settings', err));
  }

  fetchTokenAssets() {
    if (this.tokensSub) {
      try { this.tokensSub.unsubscribe(); } catch { }
      this.tokensSub = null;
    }
    this.tokensSub = this.auth.getTokenAssets().pipe(takeUntil(this.destroy$)).subscribe((res: any) => {
      this.tokenAssets = res?.data || [];
    }, (err) => console.error('Failed to load token assets', err));
  }

  isValidBscAddress(address: string): boolean {
    return /^0x[a-fA-F0-9]{40}$/.test((address || '').trim());
  }

  isValidHttpUrl(value: string): boolean {
    const v = (value || '').trim();
    if (!v) return true;
    try {
      const u = new URL(v);
      return u.protocol === 'http:' || u.protocol === 'https:';
    } catch {
      return false;
    }
  }

  saveWalletSettings() {
    if (this.savingWallet) return;
    if (!this.isValidBscAddress(this.fromAddress)) {
      this.snackBar.open('Invalid FROM_ADDRESS (must be 0x...)', 'OK', { duration: 3000 });
      return;
    }
    if (!this.privateKey || this.privateKey.trim().length < 32) {
      this.snackBar.open('PRIVATE_KEY is required', 'OK', { duration: 3000 });
      return;
    }

    if (!this.isValidHttpUrl(this.rpcUrl)) {
      this.snackBar.open('Invalid RPC URL', 'OK', { duration: 3000 });
      return;
    }

    if (this.contractAddress && !this.isValidBscAddress(this.contractAddress)) {
      this.snackBar.open('Invalid Contract Address (must be 0x...)', 'OK', { duration: 3000 });
      return;
    }

    this.savingWallet = true;
    this.auth.updateWalletSettings({
      from_address: this.fromAddress.trim(),
      private_key: this.privateKey.trim(),
      chain_id: this.chainId ?? undefined,
      rpc_url: (this.rpcUrl || '').trim() || undefined,
      contract_address: (this.contractAddress || '').trim() || undefined,
    }).subscribe((res: any) => {
      this.hasPrivateKey = true;
      this.privateKey = '';
      this.snackBar.open('Wallet settings saved', 'OK', { duration: 2500 });
    }, (err) => {
      this.snackBar.open('Failed to save wallet settings', 'OK', { duration: 3000 });
      console.error(err);
    }).add(() => {
      this.savingWallet = false;
    });
  }

  saveTokenAsset() {
    if (this.savingToken) return;
    const symbol = (this.tokenSymbol || '').trim().toUpperCase();
    if (!symbol) {
      this.snackBar.open('Symbol is required', 'OK', { duration: 2500 });
      return;
    }

    if (!this.tokenIsNative) {
      if (!this.isValidBscAddress(this.tokenAddress)) {
        this.snackBar.open('Token address is required (0x...) for ERC20', 'OK', { duration: 3000 });
        return;
      }
    }

    const decimals = this.tokenDecimals ?? 18;
    if (decimals < 0 || decimals > 36) {
      this.snackBar.open('Decimals must be 0..36', 'OK', { duration: 3000 });
      return;
    }

    this.savingToken = true;
    this.auth.upsertTokenAsset({
      symbol,
      chain_id: this.chainId ?? undefined,
      is_native: this.tokenIsNative,
      token_address: this.tokenIsNative ? null : this.tokenAddress.trim(),
      decimals,
      enabled: this.tokenEnabled,
    }).subscribe(() => {
      this.snackBar.open('Token saved', 'OK', { duration: 2000 });
      this.tokenSymbol = '';
      this.tokenIsNative = false;
      this.tokenAddress = '';
      this.tokenDecimals = 18;
      this.tokenEnabled = true;
      this.loadTokenAssets();
    }, (err) => {
      this.snackBar.open('Failed to save token', 'OK', { duration: 3000 });
      console.error(err);
    }).add(() => {
      this.savingToken = false;
    });
  }

  deleteToken(symbol: string) {
    const s = (symbol || '').trim().toUpperCase();
    if (!s) return;
    this.auth.deleteTokenAsset(s).subscribe(() => {
      this.snackBar.open('Token deleted', 'OK', { duration: 2000 });
      this.loadTokenAssets();
    }, (err) => {
      this.snackBar.open('Failed to delete token', 'OK', { duration: 3000 });
      console.error(err);
    });
  }
}
