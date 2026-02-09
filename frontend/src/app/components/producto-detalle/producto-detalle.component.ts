import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { ProductData } from '../../product-data';
import { CartService } from '../../services/cart.service';
import { PuntoAComaPipe } from '../../pipes/punto-a-coma.pipe';
import { ConvertirMonedaPipe } from '../../pipes/convertir-moneda.pipe';
import { MonedaService } from '../../services/moneda.service';
import { AuthData } from '../../auth-data';

@Component({
  selector: 'app-producto-detalle',
  standalone: true,
  imports: [CommonModule, PuntoAComaPipe, ConvertirMonedaPipe],
  templateUrl: './producto-detalle.component.html',
  styleUrl: './producto-detalle.component.css'
})
export class ProductoDetalleComponent {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private productData = inject(ProductData);
  private cartService = inject(CartService);
  monedaService = inject(MonedaService);
  private authData = inject(AuthData);

  producto: any = null;
  cantidad: number = 1;
  agregadoAlCarrito: boolean = false;
  cantidadEnCarrito: number = 0;

  constructor() {
    const id = this.route.snapshot.paramMap.get('id');

    if (id) {
      this.cargarProducto(parseInt(id));

      this.cartService.cambiosCarrito.subscribe(carrito => {
        const item = carrito.find(i => i.id === parseInt(id));
        if (item) {
          this.cantidadEnCarrito = item.cantidad;
        } else {
          this.cantidadEnCarrito = 0;
        }
      });
    }
  }
//ia
  get isAdmin() {
    if (this.authData.usuario) {
      return this.authData.usuario.rol === 'jefe';
    }
    return false;
  }
//
  cargarProducto(id: number) {
    this.productData.obtenerProductos().subscribe((resp: any) => {
      if (resp.exito) {
        this.producto = resp.productos.find((p: any) => p.id == id);
      }
    });
  }

  volver() {
    this.router.navigate(['/']);
  }

  disminuirCantidad() {
    if (this.cantidad > 1) {
      this.cantidad = this.cantidad - 1;
    }
  }

  aumentarCantidad() {
    if (this.producto && this.cantidad < this.producto.stock) {
      this.cantidad = this.cantidad + 1;
    }
  }

  agregarAlCarrito() {
    if (this.producto) {
      this.cartService.agregarAlCarrito(this.producto, this.cantidad);
      this.agregadoAlCarrito = true;

      setTimeout(() => {
        this.agregadoAlCarrito = false;
      }, 2000);
    }
  }
}
