<script setup>
import { ref, computed, onMounted } from 'vue'
</script>

<template>
    <section class="hero" aria-labelledby="hero-title">
        <header class="hero__header">
            <h1 id="hero-title" class="hero__title">Todos los dispositivos, todas las marcas</h1>
            <p class="hero__description">
                Reparación de móviles, tablets, smartwatches, vídeoconsolas, portátiles de todas las marcas y modelos.
                <span class="hero__highlight">Si necesitas arreglar otra cosa, ponte en contacto con nosotros</span>
            </p>
        </header>

        <ul class="hero__grid">
            <li class="hero__item">
                <a class="hero__link" href="/catalogo/moviles">
                    <div class="hero__image-container">
                        <img src="../../img/movil.jpg" alt="Móvil">
                        <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
                    </div>
                    <span class="hero__item-label">Móviles</span>
                </a>
            </li>

            <li class="hero__item">
                <a class="hero__link" href="#">
                    <div class="hero__image-container">
                        <img src="../../img/tablet.png" alt="Tablet">
                        <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
                    </div>
                    <span class="hero__item-label">Tablets</span>
                </a>
            </li>

            <li class="hero__item">
                <a class="hero__link" href="#">
                    <div class="hero__image-container">
                        <img src="../../img/smartwatch.avif" alt="Smartwatch">
                        <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
                    </div>
                    <span class="hero__item-label">Smartwatches</span>
                </a>
            </li>

            <li class="hero__item">
                <a class="hero__link" href="#">
                    <div class="hero__image-container">
                        <img src="../../img/videoconsola.jpg" alt="Videoconsola">
                        <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
                    </div>
                    <span class="hero__item-label">Videoconsolas</span>
                </a>
            </li>

            <li class="hero__item">
                <a class="hero__link" href="#">
                    <div class="hero__image-container">
                        <img src="../../img/portatil.jpeg" alt="">
                        <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
                    </div>
                    <span class="hero__item-label">Portátiles</span>
                </a>
            </li>
        </ul>
    </section>
</template>

<style scoped lang="scss">
.hero {
    padding: 40px 20px;

    &__header {
        margin-bottom: 40px;
    }

    &__title {
        text-transform: uppercase;
        font-weight: 700;
        font-size: 2.5rem;
        color: var(--main-color);
        margin-bottom: 15px;
        text-align: center;
    }

    &__description {
        font-size: 1.2rem;
        color: var(--text-color);
        max-width: 830px;
        margin: 0 auto;
    }

    &__highlight {
        font-size: 1.2rem;
        color: var(--main-color);
        font-weight: 600;
    }

    &__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        justify-items: center;
        list-style: none;
    }

    &__link {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
        text-decoration: none;
        transition: transform 0.3s ease;

        &:hover {
            transform: translateY(-5px);
        }

        .hero__image-container {
            position: relative;
            display: inline-block;

            img {
                width: 300px;
                height: 250px;
                object-fit: cover;
                border-radius: 15px;
                background-color: var(--secondary-background-color);
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            }

            i {
                font-size: 1.2rem;
                color: var(--main-color);
                position: absolute;
                background-color: var(--secondary-background-color);
                padding: 12px;
                border-radius: 50%;
                right: 0;
                top: 0;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            }
        }

        .hero__item-label {
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
            text-transform: uppercase;
            font-size: 1rem;
        }
    }
}
</style>