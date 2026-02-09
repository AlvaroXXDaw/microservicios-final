import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'convertirMoneda',
  standalone: true
})
export class ConvertirMonedaPipe implements PipeTransform {

  transform(valor: any, moneda: string = 'EUR'): string {

    if (isNaN(valor)) {
      return '';
    }

    let resultado = valor;

    if (moneda === 'USD') {
      resultado = Math.round(valor * 1.09 * 100) / 100 + ' $';
    } else if (moneda === 'CNY') {
      resultado = Math.round(valor * 7.86 * 100) / 100 + ' ¥';
    } else {
      resultado = Math.round(valor * 100) / 100 + ' €';
    }

    return resultado;
  }
}
