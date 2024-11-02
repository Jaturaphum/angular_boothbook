import { Component } from '@angular/core';
import { HttpClient,HttpClientModule } from '@angular/common/http';
import { MatCard, MatCardHeader ,MatCardModule} from '@angular/material/card';
import { CommonModule } from '@angular/common';
import { DataService } from '../../service/data.service';
import { HeaderComponent } from '../../component/header/header.component';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-viewprofile',
  standalone: true,
  imports: [MatCard,MatCardHeader,MatCardModule,HttpClientModule,CommonModule,HeaderComponent,MatIconModule],
  templateUrl: './viewprofile.component.html',
  styleUrl: './viewprofile.component.scss'
})
export class ViewprofileComponent {
  members: any[] = [];

  constructor(
    private dataService: DataService,
    private http: HttpClient,

  ) {}

  ngOnInit(): void {
    this.http.get<any[]>(this.dataService.apiEndpoint+'/get_members').subscribe(
      (data) => {
        this.members = data;
      },
      (error) => {
        console.error('Error fetching members:', error);
      }
    );
  }
}
