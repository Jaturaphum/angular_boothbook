import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { MatDialogModule } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatButtonModule } from '@angular/material/button';
import { HttpClientModule } from '@angular/common/http';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'add-addbooth',
  templateUrl: './addbooth.component.html',
  styleUrls: ['./addbooth.component.scss'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatButtonModule,
    HttpClientModule,
  ],
  
})
export class AddBoothDialogComponent {
  booth_name: string = '';
  booth_size: string = '';
  booth_price: number | null = null;

  constructor(
    private dialogRef: MatDialogRef<AddBoothDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { zoneId: number },
    private http: HttpClient
  ) {}

  onSubmit(): void {
    const apiUrl = 'https://wag12.bowlab.net/api/api/admin/create_booth.php';
    const newBooth = {
      booth_name: this.booth_name,
      booth_size: this.booth_size,
      booth_price: this.booth_price,
      booth_status: 'ว่าง',
      zone_id: this.data.zoneId,
    };

    this.http.post(apiUrl, newBooth).subscribe(
      () => {
        this.dialogRef.close('success');
      },
      (error) => {
        console.error('Error adding booth:', error);
        alert('เกิดข้อผิดพลาดในการเพิ่มบูธ');
      }
    );
  }

  onCancel(): void {
    this.dialogRef.close();
  }
}
