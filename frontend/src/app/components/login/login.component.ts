import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthData } from '../../auth-data';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent {
  logueado: boolean = false;
  nombreUsuario: string = "";

  private authData = inject(AuthData);
  private router = inject(Router);

  intentarLogin(email: string, password: string) {
    if (email.length > 0 && password.length > 0) {
      this.authData.login(email, password).subscribe(
        (data: any) => {
          if (data.exito) {
            this.authData.usuario = data.usuario;
            this.logueado = true;
            this.nombreUsuario = data.usuario.nombre;
            this.router.navigate(['/']);
          } else {
            alert(data.mensaje);
          }
        },
        (error: any) => {
          alert("error en el login");
        }
      );
    }
  }
}
