import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root',
})
export class ProductData {
  private http = inject(HttpClient);
  private apiUrl = 'http://localhost/DWEC/Angular/ProyectoMio/backend';

  obtenerProductos() {
    return this.http.get<any>(`${this.apiUrl}/obtener_productos.php`);
  }

  obtenerProductosPorCategoria(categoria: string) {
    return this.http.get<any>(`${this.apiUrl}/obtener_productos.php?categoria=${categoria}`);
  }

  obtenerCategorias() {
    return this.http.get<any>(`${this.apiUrl}/obtener_categorias.php`);
  }

  buscarProducto(nombre: string) {
    return this.http.get<any>(`${this.apiUrl}/buscar_productos.php?nombre=${nombre}`);
  }

  filtrarPorNombre(productos: any[], patron: string): any[] {
    if (patron === '' || patron === null || patron.trim() === '') {
      return productos;
    }

    return productos.filter(producto =>
      producto.nombre.toLowerCase().includes(patron.toLowerCase())
    );
  }

  filtrarPorPrecioMaximo(productos: any[], precioMaximoStr: string): any[] {
    if (precioMaximoStr === '' || precioMaximoStr === null) {
      return productos;
    }

    const precioMaximo = parseFloat(precioMaximoStr);
    return productos.filter(producto => parseFloat(producto.precio) <= precioMaximo);
  }
}
