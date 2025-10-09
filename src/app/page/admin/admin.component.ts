import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { DataService } from '../../services/data.service';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-admin',
  standalone: false,
  templateUrl: './admin.component.html',
  styleUrl: './admin.component.scss'
})
export class AdminComponent {

  isChoose: number = 1;
  menu: string | null = null;

  constructor(private route: ActivatedRoute, private router: Router, private dataService: DataService, private http: HttpClient) {
  }
  ngOnInit() {
    this.menu = this.route.snapshot.paramMap.get('menu');
    if (this.menu == 'product') {
      this.isChoose = 1
    } else if (this.menu == 'orders') {
      this.isChoose = 2
    };
  }

  isChooseMenu(menu: number) {
    this.isChoose = menu;
    if (menu == 1) {
      this.router.navigate(['/admin/product']);
    } else if (menu == 2) {
      this.router.navigate(['/admin/orders']);
    }
  }

}
