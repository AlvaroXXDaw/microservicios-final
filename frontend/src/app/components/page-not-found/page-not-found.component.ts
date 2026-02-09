import { Component, HostListener, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
    selector: 'app-page-not-found',
    standalone: true,
    imports: [CommonModule],
    templateUrl: './page-not-found.component.html',
    styleUrl: './page-not-found.component.css'
})
export class PageNotFoundComponent {
    private router = inject(Router);

    mouseX = 0;
    mouseY = 0;

    @HostListener('document:mousemove', ['$event'])
    alMoverMouse(e: MouseEvent) {
        this.mouseX = (e.clientX - window.innerWidth / 2) * 0.01;
        this.mouseY = (e.clientY - window.innerHeight / 2) * 0.01;
    }

    irAInicio() {
        this.router.navigate(['/']);
    }
}
