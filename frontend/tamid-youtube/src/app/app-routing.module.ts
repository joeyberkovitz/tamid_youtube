import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import {LoginComponent} from './login/login.component';
import {HomeComponent} from './home/home.component';


const routes: Routes = [
  {path: 'login', component: LoginComponent},
  {path: 'search/:query', component: HomeComponent},
  {path: 'search', redirectTo: 'search/', pathMatch: 'full'},
  {path: '', redirectTo: 'search/', pathMatch: 'full'}
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
