import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root',
})
export class AuthData {
  private http = inject(HttpClient);
  private apiUrl = 'http://localhost/DWEC/Angular/ProyectoMio/backend';

  usuario: any = null;

  constructor() {
    this.verificarSesion();
  }

  verificarSesion() {
    this.http.get(this.apiUrl + '/check_auth.php', { withCredentials: true })
      .subscribe((resp: any) => {
        if (resp.exito) {
          this.usuario = resp.usuario;
        }
      });
  }

  login(email: string, password: string) {
    return this.http.post(this.apiUrl + '/login.php',
      { email: email, password: password },
      { withCredentials: true }
    );
  }

  cerrarSesion() {
    this.usuario = null;
    return this.http.post(this.apiUrl + '/logout.php', {}, { withCredentials: true });
  }

  registrar(nombre: string, email: string, password: string) {
    return this.http.post(this.apiUrl + '/registro.php', {
      nombre: nombre,
      email: email,
      password: password
    });
  }
}
