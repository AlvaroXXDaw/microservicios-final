import { Component, inject, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { CartService, ItemCarrito } from '../../services/cart.service';
import { PuntoAComaPipe } from '../../pipes/punto-a-coma.pipe';

@Component({
  selector: 'app-cart-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, PuntoAComaPipe],
  templateUrl: './cart-modal.component.html',
  styleUrl: './cart-modal.component.css'
})
export class CartModalComponent {
  @Output() close = new EventEmitter<void>();

  cartService = inject(CartService);
  private http = inject(HttpClient);
  private apiUrl = 'http://localhost/DWEC/Angular/ProyectoMio/backend';

  itemsCarrito: ItemCarrito[] = [];
  compraExitosa = false;
  mensajeCompra = '';
  procesandoCompra = false;
  pedidoGuardado = false;

  minimoParaEnvioGratis = 50;
  precioEnvio = 3.99;
  subtotal = 0;
  envioGratis = false;
  gastosEnvio = 0;
  faltaParaEnvioGratis = 0;
  total = 0;

  constructor() {
    this.cartService.cambiosCarrito.subscribe(items => {
      this.itemsCarrito = items;
      this.calcularTotales();
    });
  }

  calcularTotales() {
    this.subtotal = this.itemsCarrito.reduce((total, item) => total + (item.precio * item.cantidad), 0);

    if (this.subtotal >= this.minimoParaEnvioGratis) {
      this.envioGratis = true;
      this.gastosEnvio = 0;
      this.faltaParaEnvioGratis = 0;
    } else {
      this.envioGratis = false;
      this.gastosEnvio = this.precioEnvio;
      this.faltaParaEnvioGratis = this.minimoParaEnvioGratis - this.subtotal;
    }

    this.total = this.subtotal + this.gastosEnvio;
  }

  actualizarCantidad(productoId: number, cambio: number) {
    const item = this.itemsCarrito.find(i => i.id === productoId);
    if (item) {
      this.cartService.actualizarCantidad(productoId, item.cantidad + cambio);
    }
  }

  eliminarItem(productoId: number) {
    this.cartService.eliminarDelCarrito(productoId);
  }

  cerrarModal() {
    this.close.emit();
  }

  comprar() {
    if (this.itemsCarrito.length === 0) {
      return;
    }

    this.compraExitosa = false;
    this.mensajeCompra = '';
    this.pedidoGuardado = false;

    if (this.direccionEnvio.trim() === '') {
      this.mensajeCompra = 'por favor, introduce una direccion de envio';
      return;
    }

    this.procesandoCompra = true;

    const purchaseData = {
      items: this.itemsCarrito.map((item: ItemCarrito) => ({
        id: item.id,
        cantidad: item.cantidad
      }))
    };

    this.http.post<any>(this.apiUrl + '/comprar.php', purchaseData, { withCredentials: true })
      .subscribe(
        (response) => {
          if (response.exito) {
            this.generarFactura();
          } else {
            this.procesandoCompra = false;
            this.mensajeCompra = response.mensaje;
          }
        }
      );
  }

  // #############################################
  // #############################################
  // ##                                         ##
  // ##          HECHO CON IA                   ##
  // ##   Generacion de QR, Factura y Direccion ##
  // ##                                         ##
  // #############################################
  // #############################################

  facturaUrl = '';
  facturaId = '';
  qrUrl = '';
  direccionEnvio = '';

  generarFactura() {
    const facturaData = {
      items: this.itemsCarrito.map((item: ItemCarrito) => ({
        nombre: item.nombre,
        precio: item.precio,
        cantidad: item.cantidad
      })),
      subtotal: this.subtotal,
      envio: this.gastosEnvio,
      total: this.total,
      direccion: this.direccionEnvio
    };

    this.http.post<any>(this.apiUrl + '/generar_factura.php', facturaData, { withCredentials: true })
      .subscribe(
        (response) => {
          if (response.exito) {
            this.facturaId = response.facturaId;
            this.facturaUrl = response.facturaUrl;
            this.qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&format=png&data=' +
              encodeURIComponent(this.facturaUrl);
            this.guardarPedido();
          } else {
            this.procesandoCompra = false;
            this.mensajeCompra = 'Error al generar la factura';
          }
        },
        () => {
          this.procesandoCompra = false;
          this.mensajeCompra = 'Error al generar la factura';
        }
      );
  }

  guardarPedido() {
    const pedidoData = {
      items: this.itemsCarrito.map((item: ItemCarrito) => ({
        id: item.id,
        cantidad: item.cantidad
      })),
      facturaId: this.facturaId,
      facturaUrl: this.facturaUrl
    };

    this.http.post<any>(this.apiUrl + '/compras.php', pedidoData, { withCredentials: true })
      .subscribe(
        (response) => {
          this.procesandoCompra = false;

          if (response.exito) {
            this.pedidoGuardado = true;
            this.mensajeCompra = 'Compra realizada con exito. Pedido guardado.';
          } else {
            this.mensajeCompra = 'Compra realizada, pero no se pudo guardar el pedido.';
          }

          this.compraExitosa = true;
          this.cartService.limpiarCarrito();

          setTimeout(() => {
            window.location.reload();
          }, 2000);
        },
        () => {
          this.procesandoCompra = false;
          this.compraExitosa = true;
          this.mensajeCompra = 'Compra realizada, pero no se pudo guardar el pedido.';
          this.cartService.limpiarCarrito();
        }
      );
  }

  // #############################################
  // ##        FIN HECHO CON IA                 ##
  // #############################################
}
