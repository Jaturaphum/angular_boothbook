import { Component, Inject, OnInit } from '@angular/core';
import { DataService } from '../../service/data.service';
import { CommonModule } from '@angular/common';
import { MatDialogRef, MatDialogModule } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';
import { FormsModule } from '@angular/forms';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field'; 
import { MatInputModule } from '@angular/material/input';
import { MatSnackBar } from '@angular/material/snack-bar';

@Component({
  selector: 'app-editzone',
  standalone: true,
  imports: [
    CommonModule, 
    MatDialogModule, 
    FormsModule, 
    ReactiveFormsModule, 
    MatFormFieldModule, 
    MatInputModule
  ],
  templateUrl: './editzone.component.html',
  styleUrls: ['./editzone.component.scss']
})
export class EditzoneComponent implements OnInit {
  zoneForm: FormGroup; // FormGroup to handle the zone form data

  constructor(
    public dialogRef: MatDialogRef<EditzoneComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { zoneId: number },
    private dataService: DataService,
    private http: HttpClient,
    private fb: FormBuilder,
    private snackBar: MatSnackBar
  ) {
    // Initialize the form with FormBuilder
    this.zoneForm = this.fb.group({
      zone_name: ['', Validators.required],
      zone_info: ['', Validators.required],
      booth_count: [0, [Validators.required, Validators.min(1)]]
    });
  }

  ngOnInit(): void {
    console.log("Received Zone ID:", this.data.zoneId);
    if (this.data.zoneId) {
      this.loadZoneData(this.data.zoneId); // Load zone data if zoneId is provided
    } else {
      alert("ไม่พบ zoneId กรุณาลองใหม่อีกครั้ง");
      this.dialogRef.close(); // Close the dialog if no zoneId is found
    }
  }

  loadZoneData(zoneId: number): void {
    const apiUrl = `${this.dataService.apiEndpoint}/get_zone/${zoneId}`;
    console.log("API URL:", apiUrl);

    this.http.get(apiUrl).subscribe(
      (data: any) => {
        console.log("Loaded Zone Data:", data);
        if (data && 'zone_name' in data && 'zone_info' in data) {
          this.zoneForm.patchValue({
            zone_name: data.zone_name,
            zone_info: data.zone_info,
            booth_count: data.booth_count
          });
        } else {
          console.error("Unexpected data format:", data);
          alert("รูปแบบข้อมูลที่ได้รับไม่ถูกต้อง");
        }
      },
      (error) => {
        console.error("Error loading zone data:", error);
        let errorMsg = "เกิดข้อผิดพลาดในการโหลดข้อมูลโซน";
        if (error.status === 0) {
          errorMsg += ": ตรวจสอบการเชื่อมต่ออินเทอร์เน็ต หรือ ปัญหาเกี่ยวกับนโยบาย CORS";
        } else {
          errorMsg += `: ${error.message}`;
        }
        alert(errorMsg);
      }
    );
  }

  save(): void {
    if (this.zoneForm.invalid) {
      this.snackBar.open('กรุณากรอกข้อมูลให้ครบถ้วน', 'ปิด', { duration: 3000 });
      return;
    }
  
    const formData = {
      zone_id: this.data.zoneId,
      zone_name: this.zoneForm.value.zone_name,
      zone_info: this.zoneForm.value.zone_info,
      booth_count: this.zoneForm.value.booth_count,
      event_id: 1
    };
  
    this.http.post('https://wag12.bowlab.net/api/api/admin/update_zone.php', formData).subscribe(
      () => {
        this.snackBar.open('แก้ไขโซนสำเร็จ', 'ปิด', { duration: 3000 });
        this.dialogRef.close('updated');
      },
      (error) => {
        console.error('Error updating zone:', error);
        this.snackBar.open('เกิดข้อผิดพลาดในการแก้ไขโซน', 'ปิด', { duration: 3000 });
      }
    );
  }
  

  close(): void {
    this.dialogRef.close();
  }
}
