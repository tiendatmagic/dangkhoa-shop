import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../../services/auth.service';
import { MatSnackBar } from '@angular/material/snack-bar';
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
export class AdminOverviewComponent implements OnInit {
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

  constructor(private auth: AuthService, private snackBar: MatSnackBar) {
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

  fromAddress: string = '';
  privateKey: string = '';
  hasPrivateKey: boolean = false;
  savingWallet: boolean = false;

  ngOnInit() {
    this.loadChartData();
    this.loadWalletSettings();
  }

  loadChartData() {
    this.auth.getOverview().subscribe((res: any) => {
      if (res.chart) {
        this.chartOptions = {
          ...this.chartOptions,
          xaxis: { ...this.chartOptions.xaxis, categories: [...res.chart.categories] },
          series: [...res.chart.series]
        };
      }
    }, (err) => {
      console.error('Lỗi khi load chart:', err);
    });
  }

  loadWalletSettings() {
    this.auth.getWalletSettings().subscribe((res: any) => {
      this.fromAddress = res?.from_address || '';
      this.hasPrivateKey = !!res?.has_private_key;
    }, (err) => {
      console.error('Failed to load wallet settings', err);
    });
  }

  isValidBscAddress(address: string): boolean {
    return /^0x[a-fA-F0-9]{40}$/.test((address || '').trim());
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

    this.savingWallet = true;
    this.auth.updateWalletSettings({
      from_address: this.fromAddress.trim(),
      private_key: this.privateKey.trim(),
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
}
