import { Component, Inject, OnInit } from '@angular/core';
import { DataService } from '../../service/data.service';
import { CommonModule } from '@angular/common';
import { MatDialogRef, MatDialogModule } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';
import { FormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';

@Component({
  selector: 'app-editbooth',
  standalone: true,
  imports: [CommonModule, MatDialogModule, FormsModule,HttpClientModule],
  templateUrl: './editbooth.component.html',
  styleUrls: ['./editbooth.component.scss']
})
export class EditBoothComponent implements OnInit {
  boothData = { booth_name: '', booth_size: '', booth_price: 0, booth_status: 'ว่าง' };

  constructor(
    public dialogRef: MatDialogRef<EditBoothComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { boothId: number },
    private dataService: DataService,
    private http: HttpClient
  ) {}

  ngOnInit(): void {
    this.loadBoothData(this.data.boothId);
  }
  
  loadBoothData(boothId: number): void {
    this.http.get(`${this.dataService.apiEndpoint}/get_booth/${boothId}`).subscribe(
      (data: any) => {
        this.boothData = { ...data };
      },
      (error) => {
        console.error("Error loading booth data:", error);
        alert("เกิดข้อผิดพลาดในการโหลดข้อมูลบูธ");
      }
    );
  }

  save(): void {
    this.http.put('https://wag12.bowlab.net/api/api/admin/update_booths.php', this.boothData).subscribe(
      () => {
        alert('แก้ไขบูธสำเร็จ');
        this.dialogRef.close('updated');
      },
      (error) => {
        console.error('Error updating booth:', error);
        alert('เกิดข้อผิดพลาดในการแก้ไขบูธ');
      }
    );
  }


  close(): void {
    this.dialogRef.close();
  }
}
