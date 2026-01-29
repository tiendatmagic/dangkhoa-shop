import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { DataService } from '../../services/data.service';
import { HttpClient } from '@angular/common/http';
import { AdminTabService } from '../../services/admin-tab.service';

@Component({
  selector: 'app-admin',
  standalone: false,
  templateUrl: './admin.component.html',
  styleUrl: './admin.component.scss'
})
export class AdminComponent {

  isChoose: number = 1;
  menu: string | null = null;

  constructor(private route: ActivatedRoute, private router: Router, private dataService: DataService, private http: HttpClient, private adminTab: AdminTabService) {
  }
  ngOnInit() {
    this.menu = this.route.snapshot.paramMap.get('menu');
    if (this.menu == 'product') {
      this.isChoose = 1
    } else if (this.menu == 'orders') {
      this.isChoose = 2
    } else if (this.menu == 'overview') {
      this.isChoose = 3
    } else if (this.menu == 'customize') {
      this.isChoose = 4
    }
    // publish the active tab so child components will fetch (and cancel previous requests)
    if (this.menu) {
      this.adminTab.select(this.menu);
    }
  }

  isChooseMenu(menu: number) {
    this.isChoose = menu;
    if (menu == 1) {
      this.router.navigate(['/admin/product']);
      this.adminTab.select('product');
    } else if (menu == 2) {
      this.router.navigate(['/admin/orders']);
      this.adminTab.select('orders');
    } else if (menu == 3) {
      this.router.navigate(['/admin/overview']);
      this.adminTab.select('overview');
    } else if (menu == 4) {
      this.router.navigate(['/admin/customize']);
      this.adminTab.select('customize');
    }
  }

}
