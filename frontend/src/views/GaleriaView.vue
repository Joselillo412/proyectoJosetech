<script setup>
import { onMounted } from 'vue'

onMounted(() => {
    // Verificamos si el script oficial de Elfsight ya está inyectado para evitar duplicados
    if (!document.querySelector('#elfsight-platform-script')) {
        const script = document.createElement('script')
        script.src = "https://elfsightcdn.com/platform.js"
        script.id = "elfsight-platform-script"
        script.async = true
        document.head.appendChild(script)
    }
})
</script>

<template>
    <main class="galeria-page">
        <header class="galeria-header fade-in">
            <div class="breadcrumb">
                <router-link to="/">Inicio</router-link> &gt; <span>Galería</span>
            </div>
            <span class="badge-tech">Feed Oficial Josetech</span>
            <h1 class="catalogo-title">Trabajos en <span>Josetech</span></h1>
            <p class="catalogo-subtitle">Nuestras últimas reparaciones y microsoldaduras compartidas en tiempo real.</p>
        </header>

        <section class="widget-container fade-in-up">
            <div class="elfsight-app-1bc38421-3535-4ed3-8928-faf6ce0c2452" data-elfsight-app-lazy></div>
        </section>
    </main>
</template>

<style scoped>
/* =========================================
   LAYOUT PRINCIPAL
========================================= */
.galeria-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: 'Inter', system-ui, sans-serif;
}

.galeria-header {
    text-align: center;
    margin-bottom: 40px;
}

.breadcrumb {
    font-size: 0.9rem;
    color: var(--text-color);
    margin-bottom: 20px;
}

.breadcrumb a {
    color: var(--main-color);
    text-decoration: none;
    font-weight: 600;
}

.badge-tech {
    display: inline-block;
    background: rgba(var(--main-color-rgb, 217, 106, 26), 0.1);
    color: var(--main-color);
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 15px;
}

.catalogo-title {
    font-size: 3rem;
    font-weight: 800;
    color: var(--text-color);
    margin-bottom: 10px;
}

.catalogo-title span {
    color: var(--main-color);
}

.catalogo-subtitle {
    font-size: 1.1rem;
    color: #64748b;
}

/* =========================================
   CONTENEDOR DEL WIDGET
========================================= */
.widget-container {
    min-height: 500px;
    /* Evita saltos bruscos en la interfaz mientras Elfsight inyecta las fotos */
    background: white;
    border-radius: 24px;
    padding: 20px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05);
    margin-bottom: 40px;
}

/* =========================================
   FOOTER SOCIAL
========================================= */
.social-footer {
    margin-top: 40px;
}

.social-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: white;
    border-radius: 20px;
    padding: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.icon-wrapper {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    flex-shrink: 0;
}

.social-text h3 {
    font-size: 1.4rem;
    margin: 0 0 5px 0;
}

.social-text p {
    color: #94a3b8;
    margin: 0;
    font-size: 0.95rem;
}

.btn-follow {
    background: var(--main-color);
    color: white;
    text-decoration: none;
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.btn-follow:hover {
    transform: translateY(-2px);
    opacity: 0.95;
}

/* =========================================
   ANIMACIONES
========================================= */
.fade-in {
    animation: fadeIn 0.5s ease-in forwards;
}

.fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .catalogo-title {
        font-size: 2.2rem;
    }

    .social-card {
        flex-direction: column;
        text-align: center;
    }

    .btn-follow {
        width: 100%;
        justify-content: center;
    }
}
</style>