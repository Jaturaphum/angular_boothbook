import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatSnackBar } from '@angular/material/snack-bar';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { MatDividerModule } from '@angular/material/divider';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
  standalone: true,
  imports: [
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    ReactiveFormsModule,
    HttpClientModule,
    MatDividerModule,
    CommonModule
  ]
})
export class LoginComponent {
  loginForm: FormGroup;

  constructor(
    private fb: FormBuilder,
    private snackBar: MatSnackBar,
    private http: HttpClient,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', Validators.required]
    });
  }

  onLogin(): void {
    if (this.loginForm.invalid) {
      this.snackBar.open('กรุณากรอกข้อมูลให้ครบถ้วน', 'ปิด', { duration: 3000 });
      return;
    }

    const loginData = this.loginForm.value;

    this.http.post('https://wag12.bowlab.net/api/api/admin/login.php', loginData).subscribe(
      (response: any) => {
        if (response.success) {
          this.snackBar.open('เข้าสู่ระบบสำเร็จ', 'ปิด', { duration: 3000 });
          sessionStorage.setItem('user', JSON.stringify(response.user_data)); // เก็บข้อมูลผู้ใช้ใน session
          this.router.navigate(['/main']);
        } else {
          this.snackBar.open(response.message, 'ปิด', { duration: 3000 });
        }
      },
      (error) => {
        console.error('Error during login:', error);
        this.snackBar.open('เกิดข้อผิดพลาดในการเข้าสู่ระบบ', 'ปิด', { duration: 3000 });
      }
    );
  }

  goToRegister() {
    this.router.navigate(['/register']);
  }
}
