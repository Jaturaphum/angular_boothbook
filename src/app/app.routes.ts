import { Routes } from '@angular/router';
import { FirstpageComponent } from './page/firstpage/firstpage.component';
import { MainComponent } from './page/main/main.component';
import { BoothpageComponent } from './page/boothpage/boothpage.component';
import { LoginComponent } from './page/login/login.component';
import { RegisterComponent } from './page/register/register.component';
import { BookingComponent } from './page/booking/booking.component';
import { ViewprofileComponent } from './page/viewprofile/viewprofile.component';

export const routes: Routes = [
    {path:'',component: FirstpageComponent},
    {path:'main',component:MainComponent},
    {path:'booths',component: BoothpageComponent},
    {path:'login',component: LoginComponent},
    {path:'register',component: RegisterComponent},
    {path:'booking',component:BookingComponent},
    {path:'viewprofile', component:ViewprofileComponent}
];
