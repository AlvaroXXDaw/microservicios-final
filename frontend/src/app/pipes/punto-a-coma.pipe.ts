import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'puntoAComa',
  standalone: true
})
export class PuntoAComaPipe implements PipeTransform {
  transform(valor: unknown): string {
    if (valor === null || valor === undefined) {
      return '';
    }

    const texto = String(valor).trim();
    if (texto === '') {
      return '';
    }

    if (texto.includes(',')) {
      return texto;
    }

    return texto.replace('.', ',');
  }
}

