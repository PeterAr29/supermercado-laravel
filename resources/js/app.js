import './bootstrap';

import Alpine from 'alpinejs';

import Swiper from 'swiper';
import { Autoplay, Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

window.Alpine = Alpine;

Alpine.start();

/**
 * Carrusel de promociones del home.
 *
 * Swiper entra por npm y no por CDN (H-17). El arranque vive aquí y no en un
 * <script> dentro de la vista: si la vista trae su propio <script src="...">,
 * el bundle deja de ser la única fuente de JavaScript y volvemos a tener dos
 * sistemas de assets conviviendo, que es lo que esta fase viene a cerrar.
 *
 * El `if` es necesario: app.js se carga en todas las páginas y el carrusel
 * solo existe en una.
 */
if (document.querySelector('.promociones-swiper')) {
    new Swiper('.promociones-swiper', {
        modules: [Autoplay, Navigation, Pagination],
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
}
