import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { AuthData } from '../../auth-data';
import { PuntoAComaPipe } from '../../pipes/punto-a-coma.pipe';

export interface CompraItem {
  compra_id: number;
  producto_id: number;
  producto_nombre: string;
  producto_imagen: string;
  cantidad: number;
  precio_unitario: number;
  total: number;
  fecha_compra: string;
  factura_id: string | null;
  factura_url: string | null;
}

@Component({
  selector: 'app-mis-pedidos',
  standalone: true,
  imports: [CommonModule, RouterLink, PuntoAComaPipe],
  templateUrl: './mis-pedidos.component.html',
  styleUrl: './mis-pedidos.component.css'
})
export class MisPedidosComponent implements OnInit {
  private http = inject(HttpClient);
  authData = inject(AuthData);
  private apiUrl = '/api';

  compras: CompraItem[] = [];
  cargando = true;
  mensajeError = '';
  totalCompras = 0;
  totalGastado = 0;

//ia
  get estaLogueado() {
    return this.authData.usuario !== null;
  }

  get esAdmin() {
    if (this.authData.usuario) {
      return this.authData.usuario.rol === 'jefe';
    }
    return false;
  }

  get nombreUsuario() {
    if (this.authData.usuario) {
      return this.authData.usuario.nombre;
    }
    return '';
  }

  ngOnInit() {
    if (this.estaLogueado && !this.esAdmin) {
      this.cargarCompras();
    } else {
      this.cargando = false;
    }
  }
//
  cargarCompras() {
    this.cargando = true;
    this.mensajeError = '';

    this.http.get<any>(this.apiUrl + '/compras.php', { withCredentials: true })
      .subscribe(
        (response) => {
          if (response.exito) {
            if (response.compras) {
              this.compras = response.compras;
            } else {
              this.compras = [];
            }
            if (response.total_compras) {
              this.totalCompras = response.total_compras;
            } else {
              this.totalCompras = 0;
            }
            if (response.total_gastado) {
              this.totalGastado = response.total_gastado;
            } else {
              this.totalGastado = 0;
            }
          } else {
            this.mensajeError = 'No se pudo cargar el historial de pedidos.';
          }
          this.cargando = false;
        },
        () => {
          this.mensajeError = 'Error de conexion al cargar pedidos.';
          this.cargando = false;
        }
      );
  }
}
