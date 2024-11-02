import { Component } from '@angular/core';
import {MatIconModule} from '@angular/material/icon';
import {MatDividerModule} from '@angular/material/divider';
import {MatButtonModule} from '@angular/material/button';
import { RouterLink} from '@angular/router';
import { DataService } from '../../service/data.service';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-firstpage',
  standalone: true,
  imports: [MatButtonModule,MatIconModule,MatDividerModule,RouterLink],
  templateUrl: './firstpage.component.html',
  styleUrl: './firstpage.component.scss'
})
export class FirstpageComponent {
  // constructor(private dataService:DataService ,private http:HttpClient){
  //   http.get(dataService.apiEndpoint+"/get_zones").subscribe
  //     ((data:any)=>console.log(data));
  // }
}
