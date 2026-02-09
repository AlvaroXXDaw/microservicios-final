import { Routes } from '@angular/router';
import { LoginComponent } from './components/login/login.component';
import { RegistroComponent } from './components/registro/registro.component';
import { HomeComponent } from './components/home/home.component';
import { AdminComponent } from './components/admin/admin.component';
import { ProductoDetalleComponent } from './components/producto-detalle/producto-detalle.component';
import { MisPedidosComponent } from './components/mis-pedidos/mis-pedidos.component';

export const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'home', redirectTo: '', pathMatch: 'full' },

  { path: 'login', component: LoginComponent },
  { path: 'registro', component: RegistroComponent },

  { path: 'producto/:id', component: ProductoDetalleComponent },
  { path: 'mis-pedidos', component: MisPedidosComponent },
  { path: 'admin', component: AdminComponent },

  { path: '**', loadComponent: () => import('./components/page-not-found/page-not-found.component').then(m => m.PageNotFoundComponent) }
];
