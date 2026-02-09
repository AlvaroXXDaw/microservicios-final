import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { ProductData } from '../../product-data';
import { SearchComponent } from '../search/search.component';
import { ConvertirMonedaPipe } from '../../pipes/convertir-moneda.pipe';
import { PuntoAComaPipe } from '../../pipes/punto-a-coma.pipe';
import { MonedaService } from '../../services/moneda.service';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SearchComponent,
    ConvertirMonedaPipe,
    PuntoAComaPipe
  ],
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css']
})
export class HomeComponent {
  private data = inject(ProductData);
  private router = inject(Router);
  monedaService = inject(MonedaService);

  productos: any[] = [];
  productosTodos: any[] = [];
  filtroPrecio = 'none';
  categorias: string[] = [];
  categoriaSeleccionada: string = '';

  constructor() {
    this.obtenerProductos();
    this.obtenerCategorias();
  }

  obtenerCategorias() {
    this.data.obtenerCategorias().subscribe((resp: any) => {
      if (resp.exito) {
        this.categorias = resp.categorias;
      }
    });
  }

  seleccionarCategoria(categoria: string) {
    this.categoriaSeleccionada = categoria;

    if (categoria === '') {
      this.data.obtenerProductos().subscribe((resp: any) => this.procesarProductos(resp));
    } else {
      this.data.obtenerProductosPorCategoria(categoria).subscribe((resp: any) => this.procesarProductos(resp));
    }
  }

  obtenerProductos() {
    this.data.obtenerProductos().subscribe((resp: any) => this.procesarProductos(resp));
  }

  private procesarProductos(resp: any) {
    if (resp.exito) {
      if (resp.productos) {
        this.productosTodos = resp.productos;
      } else {
        this.productosTodos = [];
      }
      this.ordenarPorPrecio();
    }
  }

  ordenarPorPrecio() {
    this.productos = [...this.productosTodos];

    if (this.filtroPrecio === 'asc') {
      this.productos.sort((a, b) => Number(a.precio) - Number(b.precio));
    } else if (this.filtroPrecio === 'desc') {
      this.productos.sort((a, b) => Number(b.precio) - Number(a.precio));
    }
  }

  verDetalle(id: number) {
    this.router.navigate(['/producto', id]);
  }
}
