import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject } from 'rxjs';
import { AuthData } from '../auth-data';

export interface ItemCarrito {
  id: number;
  nombre: string;
  precio: number;
  imagen: string;
  cantidad: number;
}

@Injectable({
  providedIn: 'root'
})
export class CartService {
  private http = inject(HttpClient);
  private authData = inject(AuthData);
  private apiUrl = '/api';
  private carrito: ItemCarrito[] = [];
  private carritoSubject = new BehaviorSubject<ItemCarrito[]>([]);
  private estaLogueado = false;

  cambiosCarrito = this.carritoSubject.asObservable();

  constructor() {
    if (this.authData.usuario) {
      this.estaLogueado = true;
      this.cargarCarritoServidor();
    } else {
      this.estaLogueado = false;
      this.cargarCarritoLocal();
    }
  }

  private cargarCarritoLocal() {
    const carritoGuardado = localStorage.getItem('cart');
    if (carritoGuardado) {
      this.carrito = JSON.parse(carritoGuardado);
      this.carritoSubject.next(this.carrito);
    }
  }

  private cargarCarritoServidor() {
    this.http.get<any>(this.apiUrl + '/carrito.php', { withCredentials: true })
      .subscribe({
        next: (resp) => {
          if (resp.exito) {
            this.carrito = resp.carrito;
            this.carritoSubject.next(this.carrito);
            localStorage.setItem('cart', JSON.stringify(this.carrito));
          }
        },
        error: () => {
          this.cargarCarritoLocal();
        }
      });
  }

  private guardarCarrito() {
    localStorage.setItem('cart', JSON.stringify(this.carrito));
    this.carritoSubject.next(this.carrito);
  }

  private sincronizarServidor(productoId: number, cantidad: number) {
    this.http.post(this.apiUrl + '/carrito.php',
      { producto_id: productoId, cantidad: cantidad },
      { withCredentials: true }
    ).subscribe();
  }

  private eliminarDelServidor(productoId: number) {
    this.http.delete(this.apiUrl + '/carrito.php?producto_id=' + productoId,
      { withCredentials: true }
    ).subscribe();
  }

  agregarAlCarrito(producto: any, cantidad: number = 1): void {
    const productoExistente = this.carrito.find(item => item.id === producto.id);

    if (productoExistente) {
      productoExistente.cantidad = productoExistente.cantidad + cantidad;
    } else {
      let nuevoItem: ItemCarrito = {
        id: producto.id,
        nombre: producto.nombre,
        precio: parseFloat(producto.precio),
        imagen: producto.imagen,
        cantidad: cantidad
      };
      this.carrito.push(nuevoItem);
    }

    this.guardarCarrito();

    if (this.estaLogueado) {
      const item = this.carrito.find(i => i.id === producto.id);
      if (item) {
        this.sincronizarServidor(producto.id, item.cantidad);
      } else {
        this.sincronizarServidor(producto.id, 0);
      }
    }
  }

  eliminarDelCarrito(productoId: number): void {
    this.carrito = this.carrito.filter(item => item.id !== productoId);
    this.guardarCarrito();

    if (this.estaLogueado) {
      this.eliminarDelServidor(productoId);
    }
  }

  actualizarCantidad(productoId: number, cantidad: number): void {
    const itemEncontrado = this.carrito.find(item => item.id === productoId);

    if (itemEncontrado) {
      itemEncontrado.cantidad = cantidad;

      if (itemEncontrado.cantidad <= 0) {
        this.eliminarDelCarrito(productoId);
      } else {
        this.guardarCarrito();
        if (this.estaLogueado) {
          this.sincronizarServidor(productoId, cantidad);
        }
      }
    }
  }

  obtenerCarrito(): ItemCarrito[] {
    return this.carrito;
  }

  limpiarCarrito(): void {
    this.carrito = [];
    this.guardarCarrito();

    if (this.estaLogueado) {
      this.http.delete(this.apiUrl + '/carrito.php', { withCredentials: true }).subscribe();
    }
  }
}
