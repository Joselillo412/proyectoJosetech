<script setup>
const marcas = [
    { nombre: 'Sony', logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Playstation_logo_colour.svg/3840px-Playstation_logo_colour.svg.png', ruta: '/catalogo/consolas/sony' },
    { nombre: 'Microsoft', logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f9/Xbox_one_logo.svg/960px-Xbox_one_logo.svg.png', ruta: '/catalogo/consolas/microsoft' },
    { nombre: 'Nintendo', logo: 'https://logodownload.org/wp-content/uploads/2017/04/nintendo-logo-1-1.png', ruta: '/catalogo/consolas/nintendo' },
]
</script>

<template>
    <section class="consolas">
        <div class="consolas__header">
            <h1 class="consolas__title">Reparación de consolas</h1>
            <p class="consolas__description">
                Selecciona la marca de tu dispositivo para ver los modelos disponibles.
                <span class="consolas__highlight">¿No ves tu marca? Ponte en contacto con nosotros.</span>
            </p>
        </div>

        <ul class="consolas__grid">
            <li v-for="marca in marcas" :key="marca.nombre">
                <router-link :to="marca.ruta" class="consolas__card">
                    <div class="consolas__logo-wrapper">
                        <img :src="marca.logo" :alt="marca.nombre" />
                    </div>

                </router-link>
            </li>
        </ul>
    </section>
</template>

<style scoped lang="scss">
.consolas {
    padding: 40px 20px;
    max-width: 1100px;
    margin: 0 auto;

    &__header {
        text-align: center;
        margin-bottom: 50px;
    }

    &__title {
        text-transform: uppercase;
        font-weight: 700;
        font-size: 2.5rem;
        color: var(--main-color);
        margin-bottom: 15px;
    }

    &__description {
        font-size: 1.1rem;
        color: var(--text-color);
        max-width: 700px;
        margin: 0 auto;
    }

    &__highlight {
        color: var(--main-color);
        font-weight: 600;
    }

    &__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 24px;
        list-style: none;
        padding: 0;
    }

    &__card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        padding: 30px 20px;
        background-color: var(--secondary-background-color, #fff);
        border-radius: 16px;
        border: 2px solid transparent;
        text-decoration: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        transition: all 0.25s ease;

        &:hover {
            border-color: var(--main-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 24px rgba(245, 130, 32, 0.2);

            .consolas__logo-wrapper img {
                filter: none;
            }

            .consolas__brand-name {
                color: var(--main-color);
            }
        }
    }

    &__logo-wrapper {
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;

        img {
            max-height: 56px;
            max-width: 120px;
            object-fit: contain;
            filter: grayscale(1);
            transition: filter 0.25s ease;
        }
    }

    &__brand-name {
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-color);
        transition: color 0.25s ease;
    }
}

@media (max-width: 1024px) {
    .consolas {
        &__logo-wrapper {
            img {
                filter: none;
            }
        }
    }
}
</style>