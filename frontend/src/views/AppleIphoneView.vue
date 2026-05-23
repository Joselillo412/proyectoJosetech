<script setup>
import { ref, computed, onMounted } from 'vue'

const modelos = ref([])
const cargando = ref(true)
const error = ref(false)
const busqueda = ref('')

const imagenFallback = 'https://static.vecteezy.com/system/resources/previews/060/163/319/non_2x/unforgettable-classic-a-smartphone-generic-and-modern-no-background-with-transparent-background-ultra-hd-free-png.png'

onMounted(async () => {
    try {
        const res = await fetch('https://proyectojosetech.onrender.com/api/dispositivos')
        const datos = await res.json()
                modelos.value = datos.filter(d => d.marca === 'Apple' && d.tipo === 'Móvil')
    } catch (e) {
        console.error('Error al cargar dispositivos Apple:', e)
        error.value = true
    } finally {
        cargando.value = false
    }
})

const modelosFiltrados = computed(() => {
    const q = busqueda.value.trim().toLowerCase()
    if (!q) return modelos.value
    return modelos.value.filter(d => d.modelo.toLowerCase().includes(q))
})

const limpiarBusqueda = () => { busqueda.value = '' }
</script>

<template>
    <section class="apple">

        <!-- Cabecera -->
        <div class="apple__header">
            <img class="apple__logo" src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg"
                alt="logo-Apple" />
            <p class="apple__description">
                Selecciona tu modelo para ver el presupuesto de reparación.
                <span class="apple__highlight">Reparamos todas las generaciones.</span>
            </p>
        </div>

        <!-- Buscador -->
        <div v-if="!cargando && !error" class="apple__search-wrapper">
            <div class="apple__search">
                <i class="fa-solid fa-magnifying-glass apple__search-icon"></i>
                <input v-model="busqueda" type="text" class="apple__search-input"
                    placeholder="Busca tu iPhone... (ej: iPhone 13, SE, Pro Max)" />
                <button v-if="busqueda" class="apple__search-clear" @click="limpiarBusqueda"
                    aria-label="Limpiar búsqueda">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Contador de resultados -->
            <p class="apple__results-count">
                <template v-if="busqueda">
                    {{ modelosFiltrados.length }} resultado{{ modelosFiltrados.length !== 1 ? 's' : '' }}
                    para "<strong>{{ busqueda }}</strong>"
                </template>
                <template v-else>
                    {{ modelos.length }} modelos disponibles
                </template>
            </p>
        </div>

        <!-- Cargando -->
        <div v-if="cargando" class="apple__loading">
            <div class="spinner"></div>
            <p>Cargando modelos...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="apple__error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <p>No se han podido cargar los modelos. Inténtalo de nuevo más tarde.</p>
        </div>

        <template v-else>
            <!-- Sin resultados -->
            <div v-if="modelosFiltrados.length === 0" class="apple__empty">
                <i class="fa-solid fa-mobile-screen-button"></i>
                <p>No hay ningún iPhone que coincida con "<strong>{{ busqueda }}</strong>"</p>
                <button class="apple__empty-btn" @click="limpiarBusqueda">Ver todos los modelos</button>
            </div>

            <!-- Grid de modelos -->
            <ul v-else class="apple__grid">
                <li v-for="item in modelosFiltrados" :key="item.id">
                    <router-link :to="`/presupuesto/${item.id}`" class="apple__card">
                        <div class="apple__img-wrapper">
                            <img :src="item.imagen_url ?? imagenFallback" :alt="item.modelo" class="apple__img" />
                        </div>
                        <span class="apple__model-name">{{ item.modelo }}</span>
                        <span class="apple__cta">
                            Ver presupuesto
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </router-link>
                </li>
            </ul>
        </template>

    </section>
</template>

<style scoped lang="scss">
.apple {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;

    &__header {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 40px;
    }

    &__logo {
        height: 80px;
        margin-bottom: 16px;
    }

    &__title {
        font-size: 2.5rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--main-color);
        margin-bottom: 12px;
    }

    &__description {
        font-size: 1.1rem;
        color: var(--text-color);
        max-width: 600px;
        margin: 0 auto;
    }

    &__highlight {
        color: var(--main-color);
        font-weight: 600;
    }

    &__search-wrapper {
        max-width: 560px;
        margin: 0 auto 40px;
        text-align: center;
    }

    &__search {
        display: flex;
        align-items: center;
        gap: 12px;
        background-color: var(--secondary-background-color, #fff);
        border: 2px solid #e0e0e0;
        border-radius: 50px;
        padding: 12px 20px;
        transition: border-color 0.2s;

        &:focus-within {
            border-color: var(--main-color);
            box-shadow: 0 0 0 4px rgba(245, 130, 32, 0.1);
        }
    }

    &__search-icon {
        color: #aaa;
        font-size: 1rem;
        flex-shrink: 0;
    }

    &__search-input {
        flex: 1;
        border: none;
        outline: none;
        background: transparent;
        font-size: 1rem;
        color: var(--text-color);

        &::placeholder {
            color: #bbb;
        }
    }

    &__search-clear {
        background: none;
        border: none;
        cursor: pointer;
        color: #aaa;
        font-size: 1rem;
        padding: 0;
        display: flex;
        align-items: center;
        transition: color 0.2s;

        &:hover {
            color: var(--main-color);
        }
    }

    &__results-count {
        margin-top: 12px;
        font-size: 0.88rem;
        color: #999;

        strong {
            color: var(--text-color);
        }
    }

    &__loading {
        text-align: center;
        padding: 80px 0;
        color: var(--text-color);

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--main-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    }

    &__error {
        text-align: center;
        padding: 80px 0;
        color: #e74c3c;

        i {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 16px;
        }
    }

    &__empty {
        text-align: center;
        padding: 80px 20px;
        color: #aaa;

        i {
            font-size: 3rem;
            display: block;
            margin-bottom: 20px;
        }

        p {
            font-size: 1rem;
            margin-bottom: 24px;

            strong {
                color: var(--text-color);
            }
        }
    }

    &__empty-btn {
        background: var(--main-color);
        color: #fff;
        border: none;
        border-radius: 50px;
        padding: 12px 28px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;

        &:hover {
            opacity: 0.85;
        }
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
        gap: 14px;
        padding: 24px 16px 20px;
        background-color: var(--secondary-background-color, #fff);
        border-radius: 16px;
        border: 2px solid transparent;
        text-decoration: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        transition: all 0.25s ease;

        &:hover {
            border-color: var(--main-color);
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(245, 130, 32, 0.18);

            .apple__cta {
                color: var(--main-color);
            }
        }
    }

    &__img-wrapper {
        height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    &__img {
        height: 140px;
        width: auto;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    &__model-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-color);
        text-align: center;
    }

    &__cta {
        font-size: 0.78rem;
        font-weight: 600;
        color: #bbb;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: color 0.25s ease;
    }
}
</style>