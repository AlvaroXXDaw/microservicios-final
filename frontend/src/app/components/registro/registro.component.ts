import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthData } from '../../auth-data';

@Component({
  selector: 'app-registro',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './registro.component.html',
  styleUrl: './registro.component.css'
})
export class RegistroComponent {
  private authData = inject(AuthData);
  private router = inject(Router);

  registrarse(nombre: string, email: string, password: string, passwordConfirm: string) {
    if (nombre.length === 0) {
      alert('El nombre es obligatorio');
      return;
    }

    if (email.length === 0) {
      alert('El email es obligatorio');
      return;
    }

    if (password.length === 0) {
      alert('La contraseña es obligatoria');
      return;
    }

    if (passwordConfirm.length === 0) {
      alert('Debes confirmar la contraseña');
      return;
    }

    if (password !== passwordConfirm) {
      alert('Las contraseñas no coinciden');
      return;
    }

    if (password.length < 6) {
      alert('La contraseña debe tener al menos 6 caracteres');
      return;
    }

    this.authData.registrar(nombre, email, password).subscribe(
      (data: any) => {
        console.log('Respuesta del servidor:', data);

        if (data.exito) {
          alert('¡Cuenta creada exitosamente! Ahora puedes iniciar sesión.');
          this.router.navigate(['/login']);
        } else {
          alert(data.mensaje);
        }
      },
      (error: any) => {
        console.error('Error completo:', error);

        let mensajeError = 'Error al crear la cuenta. Por favor, intenta de nuevo.';

        if (error.error) {
          if (error.error.mensaje) {
            mensajeError = error.error.mensaje;
          }
        } else if (error.message) {
          mensajeError = error.message;
        }

        alert(mensajeError);
      }
    );
  }
}
