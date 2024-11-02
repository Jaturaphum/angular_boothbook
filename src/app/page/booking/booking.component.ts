import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { DataService } from '../../service/data.service';
import { CommonModule } from '@angular/common';
import { HeaderComponent } from '../../component/header/header.component';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-booking',
  standalone: true,
  imports: [CommonModule, HttpClientModule, HeaderComponent,FormsModule],
  templateUrl: './booking.component.html',
  styleUrls: ['./booking.component.scss']
})
export class BookingComponent implements OnInit {
  bookings: any[] = [];
  isLoading = true;
  errorMessage: string | null = null;
  selectedStatus: string = ''; // ตัวแปรเก็บสถานะที่เลือก

  constructor(private dataService: DataService, private http: HttpClient) {}

  ngOnInit(): void {
    this.loadBookingsByStatus();
  }

  async loadBookingsByStatus(): Promise<void> {
    try {
      this.isLoading = true;
      this.errorMessage = null;
  
      // If there's no selected status, load all bookings
      const url = this.selectedStatus 
        ? `${this.dataService.apiEndpoint}/get_bookings_by_status/${this.selectedStatus}`
        : `${this.dataService.apiEndpoint}/get_bookings`;
  
      const data = await this.http.get<any[]>(url).toPromise();
      this.bookings = data ?? []; // Use an empty array if data is undefined
    } catch (error) {
      console.error('Error fetching bookings:', error);
      this.errorMessage = 'ไม่สามารถดึงข้อมูลการจองได้ กรุณาลองใหม่อีกครั้ง';
      
      this.bookings = []; // Set bookings to an empty array in case of error
    } finally {
      this.isLoading = false;
    }
  }
}
