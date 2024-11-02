import { Component, OnInit } from '@angular/core';
import { HeaderComponent } from '../../component/header/header.component';
import { FooterComponent } from '../../component/footer/footer.component';
import { HttpClientModule, HttpClient } from '@angular/common/http';
import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { MatListModule } from '@angular/material/list';
import { DataService } from '../../service/data.service';
import { Convert as zonesCvt, Zones } from '../../model/zones.model';
import { Convert as boothsCvt, Booths } from '../../model/booths.moel'; // Corrected import for booths.model
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { EditzoneComponent } from '../editzone/editzone.component';

@Component({
  selector: 'app-main',
  standalone: true,
  imports: [
    HeaderComponent,
    FooterComponent,
    HttpClientModule,
    MatListModule,
    CommonModule,
    RouterModule,
    EditzoneComponent,
    MatDialogModule,
  ],
  templateUrl: './main.component.html',
  styleUrls: ['./main.component.scss'],
})
export class MainComponent implements OnInit {
  zones: Zones[] = [];
  booths: Booths[] = [];
  selectedZone: any;

  constructor(
    private dataService: DataService,
    private http: HttpClient,
    private dialog: MatDialog
  ) {
    // Initial data fetch for zones
    this.loadZones();
  }

  ngOnInit(): void {
    this.loadZones(); // Ensure zones are loaded on initialization
  }

  loadZones(): void {
    this.http.get(this.dataService.apiEndpoint + '/get_zones').subscribe(
      (data: any) => {
        this.zones = zonesCvt.toZones(JSON.stringify(data));
        console.log('Loaded Zones:', this.zones);
      },
      (error) => {
        console.error('Error loading zones:', error);
      }
    );
  }

  viewDetail(zoneId: number): void {
    console.log('Selected Zone ID:', zoneId);
    this.http
      .get(`${this.dataService.apiEndpoint}/get_booths_by_zone/${zoneId}`)
      .subscribe(
        (data: any) => {
          this.booths = boothsCvt.toBooths(JSON.stringify(data)); // Convert and store booth data
          console.log('Booths in Zone:', this.booths);
        },
        (error) => {
          console.error('Error fetching booths:', error);
        }
      );
  }

  editZone(zoneId: number): void {
    const dialogRef = this.dialog.open(EditzoneComponent, {
      data: { zoneId },
      width: '500px',
    });

    dialogRef.afterClosed().subscribe((result: any) => {
      if (result === 'updated') {
        this.loadZones();
      }
    });
  }

  deleteZone(zoneId: number): void {
    const confirmDelete = confirm('คุณแน่ใจว่าต้องการลบโซนนี้หรือไม่?');

    if (confirmDelete) {
      const apiUrl = `https://wag12.bowlab.net/api/api/admin/delete_zone.php`;
      this.http.delete(apiUrl, { body: { zone_id: zoneId } }).subscribe(
        () => {
          alert('ลบโซนสำเร็จ');
          this.zones = this.zones.filter((zone) => zone.zone_id !== zoneId);
        },
        (error) => {
          console.error('Error deleting zone:', error);
          alert('เกิดข้อผิดพลาดในการลบโซน');
        }
      );
    }
  }
}
