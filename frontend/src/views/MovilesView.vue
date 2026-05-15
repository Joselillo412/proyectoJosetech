<script setup>
const marcas = [
    { nombre: 'Apple', logo: 'https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg', ruta: '/catalogo/moviles/apple' },
    { nombre: 'Samsung', logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Samsung_wordmark.svg/1920px-Samsung_wordmark.svg.png', ruta: '/catalogo/moviles/samsung' },
    { nombre: 'Xiaomi', logo: 'https://upload.wikimedia.org/wikipedia/commons/2/29/Xiaomi_logo.svg', ruta: '/catalogo/moviles/xiaomi' },
    { nombre: 'Google', logo: 'https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg', ruta: '/catalogo/moviles/google' },
    { nombre: 'OnePlus', logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/OP_LU_Reg_1L_RGB_red_copy-01.svg/1920px-OP_LU_Reg_1L_RGB_red_copy-01.svg.png', ruta: '/catalogo/moviles/oneplus' },
    { nombre: 'Huawei', logo: 'https://static.vecteezy.com/system/resources/previews/068/764/269/non_2x/huawei-logo-mobile-company-brand-official-icon-transparent-background-free-png.png', ruta: '/catalogo/moviles/huawei' },
    { nombre: 'Honor', logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/96/LOGO_Honor.svg/3840px-LOGO_Honor.svg.png', ruta: '/catalogo/moviles/honor' },
    { nombre: 'Nothing', logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/30/Nothing.svg/1280px-Nothing.svg.png?utm_source=commons.wikimedia.org&utm_campaign=index&utm_content=thumbnail', ruta: '/catalogo/moviles/nothing' },
    { nombre: 'Motorola', logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/de/Motorola.svg/1920px-Motorola.svg.png', ruta: '/catalogo/moviles/motorola' },
    { nombre: 'Poco', logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/29/POCO_logo.svg/1920px-POCO_logo.svg.png', ruta: '/catalogo/moviles/poco' },
    { nombre: 'Vivo', logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/13/Vivo_logo_2019.svg/1280px-Vivo_logo_2019.svg.png', ruta: '/catalogo/moviles/vivo' },
    { nombre: 'Oppo', logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0a/OPPO_LOGO_2019.svg/1280px-OPPO_LOGO_2019.svg.png', ruta: '/catalogo/moviles/oppo' },
    { nombre: 'Realme', logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e6/Realme_logo_SVG.svg/1920px-Realme_logo_SVG.svg.png', ruta: '/catalogo/moviles/realme' }
]
</script>

<template>
    <section class="moviles">
        <div class="moviles__header">
            <h1 class="moviles__title">Reparación de Móviles</h1>
            <p class="moviles__description">
                Selecciona la marca de tu dispositivo para ver los modelos disponibles.
                <span class="moviles__highlight">¿No ves tu marca? Ponte en contacto con nosotros.</span>
            </p>
        </div>

        <ul class="moviles__grid">
            <li v-for="marca in marcas" :key="marca.nombre">
                <router-link :to="marca.ruta" class="moviles__card">
                    <div class="moviles__logo-wrapper">
                        <img :src="marca.logo" :alt="marca.nombre" />
                    </div>

                </router-link>
            </li>
        </ul>
    </section>
</template>

<style scoped lang="scss">
.moviles {
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

            .moviles__logo-wrapper img {
                filter: none;
            }

            .moviles__brand-name {
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
    .moviles {
        &__logo-wrapper {
            img {
                filter: none;
            }
        }
    }
}
</style>