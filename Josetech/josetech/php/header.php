<header>
    <div class="header-left">
        <img src="./img/josetech_sin_fondo.png" alt="Josetech Logo" class="logo">
    </div>

    <div class="header-right">
        <!-- Botón menú hamburguesa (solo visible en móvil) -->
        <button class="menu-toggle" id="menu-toggle" aria-label="Abrir menú" aria-expanded="false">&#9776;</button>

        <nav id="nav-menu" class="nav-desktop">
            <a href="./contacto.html">Contacto</a>
            <a href="./reparaciones.html">Reparaciones</a>
            <a href="./lista.html">Lista de dispositivos</a>
        </nav>
    </div>
</header>

<!-- Overlay para cerrar el menú tocando fuera -->
<div id="menu-overlay" class="menu-overlay"></div>

<script>
    const toggle = document.getElementById('menu-toggle');
    const nav = document.getElementById('nav-menu');
    const overlay = document.getElementById('menu-overlay');
    const links = nav.querySelectorAll('a');

    function openMenu() {
        nav.classList.add('active');
        overlay.classList.add('active');
        toggle.setAttribute('aria-expanded', 'true');
        // opcional: evitar scroll del body cuando esté abierto
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        nav.classList.remove('active');
        overlay.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', () => {
        if (nav.classList.contains('active')) closeMenu();
        else openMenu();
    });

    // Cerrar al tocar fuera
    overlay.addEventListener('click', closeMenu);

    // Cerrar al pulsar cualquier enlace del menú (útil en móvil)
    links.forEach(a => a.addEventListener('click', closeMenu));
</script>
