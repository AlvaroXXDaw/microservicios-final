import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { ProductData } from '../../product-data';
import { PuntoAComaPipe } from '../../pipes/punto-a-coma.pipe';

@Component({
  selector: 'app-search',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, PuntoAComaPipe],
  templateUrl: './search.component.html',
  styleUrl: './search.component.css'
})
export class SearchComponent {
  private data = inject(ProductData);
  productos: any[] = [];
  buscar = '';

  alBuscar() {
    if (this.buscar.trim() === '') {
      this.productos = [];
      return;
    }

    this.data.buscarProducto(this.buscar).subscribe((resp: any) => {
      if (resp.exito) {
        this.productos = resp.productos;
      }
    });
  }

  limpiar() {
    this.buscar = '';
    this.productos = [];
  }
}
