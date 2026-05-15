/* carousel.js
   Carrusel de imágenes para Josetech
   - Requiere los IDs: #carousel, #carousel-track, #carousel-prev, #carousel-next, #carousel-indicators
   - Diseñado para usarse con el HTML del index.php que te pasé antes
*/

(function () {
    'use strict';

    // Espera que el DOM esté listo (si usas <script defer> esto también está OK)
    document.addEventListener('DOMContentLoaded', () => {
        const carousel = document.getElementById('carousel');
        const track = document.getElementById('carousel-track');
        if (!carousel || !track) return; // salir si no está el carrusel en la página

        const slides = Array.from(track.querySelectorAll('.carousel-slide'));
        const prevBtn = document.getElementById('carousel-prev');
        const nextBtn = document.getElementById('carousel-next');
        const indicatorsContainer = document.getElementById('carousel-indicators');
        const indicators = indicatorsContainer ? Array.from(indicatorsContainer.querySelectorAll('.indicator')) : [];
        if (slides.length === 0) return;

        let currentIndex = 0;
        let slideWidth = slides[0].getBoundingClientRect().width;
        const autoplayDelay = 4500; // ms
        const transitionTime = 700; // ms (debe coincidir con CSS)
        let autoplayId = null;

        // Variables para drag / swipe
        let isDragging = false;
        let startX = 0;
        let prevTranslate = 0;

        // debounce para resize
        let resizeTimeout = null;

        /* ------------------------
           Posicionamiento inicial
           ------------------------ */
        function setSlidePositions() {
            slideWidth = slides[0].getBoundingClientRect().width;
            
            // Hemos eliminado la línea s.style.left que causaba el conflicto
            // slides.forEach((s, index) => {
            //     s.style.left = (index * slideWidth) + 'px';
            // });

            moveToSlide(currentIndex, false);
        }

        function moveToSlide(index, withTransition = true) {
            if (index < 0) index = 0;
            if (index >= slides.length) index = slides.length - 1;
            const x = - index * slideWidth;
            if (withTransition) {
                track.style.transition = `transform ${transitionTime}ms cubic-bezier(.2,.8,.2,1)`;
            } else {
                track.style.transition = 'none';
            }
            track.style.transform = `translateX(${x}px)`;
            currentIndex = index;
            updateIndicators();
        }

        function updateIndicators() {
            indicators.forEach((btn, i) => {
                const selected = i === currentIndex;
                btn.classList.toggle('active', selected);
                btn.setAttribute('aria-selected', selected ? 'true' : 'false');
            });
        }

        function nextSlide() {
            const next = (currentIndex + 1) % slides.length;
            moveToSlide(next);
        }
        function prevSlide() {
            const prev = (currentIndex - 1 + slides.length) % slides.length;
            moveToSlide(prev);
        }

        /* ------------------------
           Autoplay
           ------------------------ */
        function startAutoplay() {
            stopAutoplay();
            autoplayId = setInterval(nextSlide, autoplayDelay);
        }
        function stopAutoplay() {
            if (autoplayId) { clearInterval(autoplayId); autoplayId = null; }
        }
        function resetAutoplay() {
            stopAutoplay();
            setTimeout(startAutoplay, 2000);
        }

        /* ------------------------
           Listeners controles & indicadores
           ------------------------ */
        if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetAutoplay(); });
        if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetAutoplay(); });

        indicators.forEach(btn => {
            btn.addEventListener('click', () => {
                const to = parseInt(btn.dataset.slideTo, 10);
                if (Number.isFinite(to)) {
                    moveToSlide(to);
                    resetAutoplay();
                }
            });
        });

        // pause on hover/focus
        carousel.addEventListener('mouseenter', stopAutoplay);
        carousel.addEventListener('mouseleave', startAutoplay);
        carousel.addEventListener('focusin', stopAutoplay);
        carousel.addEventListener('focusout', startAutoplay);

        // keyboard
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') { prevSlide(); resetAutoplay(); }
            if (e.key === 'ArrowRight') { nextSlide(); resetAutoplay(); }
        });

        /* ------------------------
           Touch / Pointer (swipe)
           Usamos Pointer Events cuando estén disponibles (unifica mouse + touch)
           ------------------------ */
        const supportsPointer = !!window.PointerEvent;

        function pointerDown(clientX) {
            isDragging = true;
            startX = clientX;
            prevTranslate = -currentIndex * slideWidth;
            track.style.transition = 'none';
        }
        function pointerMove(clientX) {
            if (!isDragging) return;
            const diff = clientX - startX;
            track.style.transform = `translateX(${prevTranslate + diff}px)`;
        }
        function pointerUp(clientX) {
            if (!isDragging) return;
            isDragging = false;
            const moved = clientX - startX;
            // umbral para cambio de slide
            if (moved < -50) nextSlide();
            else if (moved > 50) prevSlide();
            else moveToSlide(currentIndex);
            resetAutoplay();
        }

        if (supportsPointer) {
            track.addEventListener('pointerdown', (e) => {
                // evitar interacción con botones dentro del slide
                if (e.button !== 0) return; // solo botón izquierdo
                pointerDown(e.clientX);
                // capturar el pointer para recibir pointerup incluso fuera del elemento
                (e.target).setPointerCapture && e.target.setPointerCapture(e.pointerId);
            }, { passive: true });

            track.addEventListener('pointermove', (e) => { pointerMove(e.clientX); }, { passive: true });
            track.addEventListener('pointerup', (e) => { pointerUp(e.clientX); }, { passive: true });
            track.addEventListener('pointercancel', (e) => { pointerUp(e.clientX); }, { passive: true });
        } else {
            // Fallback: touch + mouse
            track.addEventListener('touchstart', (e) => {
                const t = e.touches[0];
                pointerDown(t.clientX);
            }, { passive: true });

            track.addEventListener('touchmove', (e) => {
                const t = e.touches[0];
                pointerMove(t.clientX);
            }, { passive: true });

            track.addEventListener('touchend', (e) => {
                const t = (e.changedTouches && e.changedTouches[0]) || {};
                pointerUp(t.clientX || startX);
            });

            track.addEventListener('mousedown', (e) => {
                if (e.button !== 0) return;
                pointerDown(e.clientX);
            });

            window.addEventListener('mousemove', (e) => { pointerMove(e.clientX); });
            window.addEventListener('mouseup', (e) => { pointerUp(e.clientX); });
        }

        /* ------------------------
           Resize: recalcular dimensiones
           ------------------------ */
        window.addEventListener('resize', () => {
            // debounce simple
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                setSlidePositions();
            }, 120);
        });

        /* ------------------------
           Inicialización
           ------------------------ */
        setSlidePositions();
        updateIndicators();
        startAutoplay();
    });
})();
