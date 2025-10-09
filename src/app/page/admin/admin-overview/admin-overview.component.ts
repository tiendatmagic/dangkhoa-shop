import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../../services/auth.service';
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

  constructor(private auth: AuthService) {
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

  ngOnInit() {
    this.loadChartData();
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
}
