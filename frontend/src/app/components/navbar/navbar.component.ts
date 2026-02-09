import { Component, inject } from '@angular/core';
import { RouterLink, RouterLinkActive, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CartService } from '../../services/cart.service';
import { CartModalComponent } from '../cart-modal/cart-modal.component';
import { AuthData } from '../../auth-data';
import { MonedaService } from '../../services/moneda.service';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [RouterLink, RouterLinkActive, CommonModule, FormsModule, CartModalComponent],
  templateUrl: './navbar.component.html',
  styleUrl: './navbar.component.css'
})
export class NavbarComponent {
  mostrarModalCarrito = false;
  cantidadItemsCarrito: number = 0;

  cartService = inject(CartService);
  authData = inject(AuthData);
  router = inject(Router);
  private monedaService = inject(MonedaService);

  get monedaSeleccionada() {
    return this.monedaService.moneda;
  }
  set monedaSeleccionada(valor: string) {
    this.monedaService.moneda = valor;
  }

  constructor() {
    this.cartService.cambiosCarrito.subscribe(items => {
      this.cantidadItemsCarrito = items.reduce((total, item) => total + item.cantidad, 0);
    });
  }
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

  get enPaginaAuth() {
    return this.router.url === '/login' || this.router.url === '/registro';
  }
  //
  alternarCarrito() {
    this.mostrarModalCarrito = !this.mostrarModalCarrito;
  }

  cerrarCarrito() {
    this.mostrarModalCarrito = false;
  }

  cerrarSesion() {
    this.authData.cerrarSesion().subscribe(() => {
      this.router.navigate(['/login']);
    });
  }
}
