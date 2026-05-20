<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const dispositivos = ref([])
const busqueda = ref('')
const cargando = ref(true)

// Configuración de Paginación
const paginaActual = ref(1)
const itemsPorPagina = 20

// Volver a la página 1 si el usuario escribe en el buscador
watch(busqueda, () => {
    paginaActual.value = 1
})

// Cargar el catálogo completo al entrar
onMounted(async () => {
    try {
        const res = await fetch('http://127.0.0.1:8000/api/dispositivos', {
            headers: { 'Accept': 'application/json' }
        })
        if (res.ok) {
            dispositivos.value = await res.json()
        }
    } catch (error) {
        console.error('Error cargando dispositivos:', error)
    } finally {
        cargando.value = false
    }
})

// Filtro general por búsqueda
const dispositivosFiltrados = computed(() => {
    if (!busqueda.value.trim()) return dispositivos.value
    const term = busqueda.value.toLowerCase()
    return dispositivos.value.filter(d =>
        d.marca.toLowerCase().includes(term) ||
        d.modelo.toLowerCase().includes(term)
    )
})

// Cálculos de Paginación
const totalPaginas = computed(() => {
    return Math.ceil(dispositivosFiltrados.value.length / itemsPorPagina) || 1
})

const dispositivosPaginados = computed(() => {
    const inicio = (paginaActual.value - 1) * itemsPorPagina
    const fin = inicio + itemsPorPagina
    return dispositivosFiltrados.value.slice(inicio, fin)
})

const cambiarPagina = (delta) => {
    const nuevaPagina = paginaActual.value + delta
    if (nuevaPagina >= 1 && nuevaPagina <= totalPaginas.value) {
        paginaActual.value = nuevaPagina
    }
}

// Redirección si elige uno de la Base de Datos
const seleccionarDispositivo = (disp) => {
    router.push(`/presupuesto/${disp.id}`)
}

// Redirección si el móvil no existe en la Base de Datos
const continuarManual = () => {
    if (!busqueda.value.trim()) return
    router.push({ path: '/presupuesto/custom', query: { modelo: busqueda.value.trim() } })
}
</script>

<template>
    <main class="solicitud fade-in">

        <header class="solicitud__cabecera">
            <span class="solicitud__etiqueta-paso" aria-label="Paso 1 de 2">Paso 1 de 2</span>
            <h1 id="titulo-buscador" class="solicitud__titulo">¿Qué equipo necesitas reparar?</h1>
            <p class="solicitud__descripcion">Busca tu modelo en nuestro catálogo o escríbelo si no lo encuentras en la
                lista.</p>
        </header>

        <section class="solicitud__buscador" aria-labelledby="titulo-buscador">
            <div class="solicitud__buscador-contenedor">
                <i class="fa-solid fa-magnifying-glass solicitud__buscador-icono" aria-hidden="true"></i>
                <input type="search" v-model="busqueda" placeholder="Ej: iPhone 13 Pro, Samsung S23..."
                    class="solicitud__buscador-input" aria-label="Buscar dispositivo por marca o modelo" autofocus>
                <button v-if="busqueda" @click="busqueda = ''" class="solicitud__buscador-limpiar" type="button"
                    aria-label="Borrar búsqueda">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        </section>

        <div v-if="cargando" class="solicitud__estado" role="status" aria-live="polite">
            <span class="solicitud__spinner" aria-hidden="true"></span>
            <p>Cargando catálogo...</p>
        </div>

        <section v-else class="solicitud__resultados" aria-live="polite">

            <transition-group name="lista" tag="div" class="solicitud__cuadricula" role="list">
                <button v-for="disp in dispositivosPaginados" :key="disp.id" class="solicitud__tarjeta" type="button"
                    role="listitem" :aria-label="`Seleccionar ${disp.marca} ${disp.modelo}`"
                    @click="seleccionarDispositivo(disp)">
                    <div class="solicitud__tarjeta-imagen" aria-hidden="true">
                        <img v-if="disp.imagen_url" :src="disp.imagen_url" :alt="disp.modelo">
                        <i v-else class="fa-solid fa-mobile-screen solicitud__tarjeta-icono-placeholder"></i>
                    </div>
                    <div class="solicitud__tarjeta-info">
                        <span class="solicitud__tarjeta-marca">{{ disp.marca }}</span>
                        <h3 class="solicitud__tarjeta-modelo">{{ disp.modelo }}</h3>
                    </div>
                    <div class="solicitud__tarjeta-flecha" aria-hidden="true">
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                </button>
            </transition-group>

            <nav class="solicitud__paginacion" v-if="totalPaginas > 1 && dispositivosPaginados.length > 0"
                aria-label="Paginación de resultados">
                <button @click="cambiarPagina(-1)" :disabled="paginaActual === 1" class="solicitud__btn-pagina"
                    type="button" aria-label="Ir a la página anterior">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Anterior
                </button>
                <span class="solicitud__paginacion-info" aria-live="polite">Página {{ paginaActual }} de {{ totalPaginas
                    }}</span>
                <button @click="cambiarPagina(1)" :disabled="paginaActual === totalPaginas"
                    class="solicitud__btn-pagina" type="button" aria-label="Ir a la página siguiente">
                    Siguiente <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            </nav>

            <article v-if="dispositivosFiltrados.length === 0 && busqueda.trim() !== ''"
                class="solicitud__alerta-manual fade-in">
                <div class="solicitud__alerta-icono" aria-hidden="true">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div class="solicitud__alerta-texto">
                    <h3>No encontramos "{{ busqueda }}"</h3>
                    <p>¡No pasa nada! Haz clic en continuar y cuéntanos qué le pasa a tu equipo.</p>
                </div>
                <button @click="continuarManual" class="solicitud__btn-manual" type="button">
                    Continuar con este equipo <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </button>
            </article>

        </section>
    </main>
</template>

<style scoped lang="scss">
/* ==========================================================================
   METODOLOGÍA BEM (Block, Element, Modifier) 
   ========================================================================== */

.solicitud {
    max-width: 900px;
    margin: 40px auto;
    padding: 40px 20px;
    min-height: 70vh;

    &__cabecera {
        text-align: center;
        margin-bottom: 40px;
    }

    &__etiqueta-paso {
        background-color: #f1f5f9;
        color: var(--main-color);
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        margin-bottom: 15px;
    }

    &__titulo {
        font-size: 2.5rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 10px 0;
    }

    &__descripcion {
        color: #64748b;
        font-size: 1.1rem;
    }

    /* --- Buscador --- */
    &__buscador {
        margin-bottom: 30px;
    }

    &__buscador-contenedor {
        position: relative;
        max-width: 600px;
        margin: 0 auto;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        border-radius: 16px;
        background: white;
        transition: all 0.3s;

        &:focus-within {
            box-shadow: 0 15px 35px rgba(var(--main-color-rgb, 217, 106, 26), 0.15);
            transform: translateY(-2px);
        }
    }

    &__buscador-icono {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.2rem;
    }

    &__buscador-input {
        width: 100%;
        padding: 20px 50px;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        font-size: 1.1rem;
        font-weight: 600;
        outline: none;
        transition: border-color 0.3s;
        color: #0f172a;
        font-family: inherit;

        /* Evita el aspa por defecto del input search en algunos navegadores para usar la nuestra */
        &::-webkit-search-cancel-button {
            display: none;
        }

        &:focus {
            border-color: var(--main-color);
        }

        &::placeholder {
            color: #cbd5e1;
            font-weight: 500;
        }
    }

    &__buscador-limpiar {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: #f1f5f9;
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        color: #64748b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s, color 0.2s;

        &:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        &:focus-visible {
            outline: 2px solid var(--main-color);
            outline-offset: 2px;
        }
    }

    /* --- Cuadrícula de Resultados --- */
    &__cuadricula {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    &__tarjeta {
        /* Reseteo de estilos de botón */
        appearance: none;
        background: white;
        text-align: left;
        font-family: inherit;
        width: 100%;
        /* Estilos de la tarjeta */
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);

        &:hover {
            border-color: var(--main-color);
            box-shadow: 0 10px 20px rgba(var(--main-color-rgb, 217, 106, 26), 0.1);
            transform: translateY(-3px);

            .solicitud__tarjeta-flecha {
                color: var(--main-color);
                transform: translateX(3px);
            }
        }

        &:focus-visible {
            outline: 3px solid var(--main-color);
            outline-offset: 2px;
            border-color: var(--main-color);
        }
    }

    &__tarjeta-imagen {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px;

        img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    }

    &__tarjeta-icono-placeholder {
        font-size: 1.8rem;
        color: #cbd5e1;
    }

    &__tarjeta-info {
        flex-grow: 1;
    }

    &__tarjeta-marca {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        display: block;
    }

    &__tarjeta-modelo {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }

    &__tarjeta-flecha {
        color: #cbd5e1;
        transition: all 0.3s;
    }

    /* --- Alerta de Entrada Manual --- */
    &__alerta-manual {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 30px;
        text-align: center;
        margin-top: 20px;
    }

    &__alerta-icono {
        width: 60px;
        height: 60px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #94a3b8;
        margin: 0 auto 15px auto;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    &__alerta-texto {
        margin-bottom: 20px;

        h3 {
            margin: 0 0 5px 0;
            color: #0f172a;
            font-size: 1.3rem;
        }

        p {
            margin: 0;
            color: #64748b;
        }
    }

    &__btn-manual {
        background: var(--main-color);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s, background-color 0.2s;
        font-family: inherit;

        &:hover {
            background: var(--secondary-color, #b55612);
            transform: translateY(-2px);
        }

        &:focus-visible {
            outline: 3px solid #0f172a;
            outline-offset: 3px;
        }
    }

    /* --- Paginación --- */
    &__paginacion {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
    }

    &__btn-pagina {
        background: white;
        border: 1px solid #cbd5e1;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: inherit;

        &:hover:not(:disabled) {
            border-color: var(--main-color);
            color: var(--main-color);
            box-shadow: 0 4px 10px rgba(var(--main-color-rgb, 217, 106, 26), 0.1);
        }

        &:focus-visible {
            outline: 2px solid var(--main-color);
            outline-offset: 2px;
        }

        &:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8fafc;
        }
    }

    &__paginacion-info {
        font-weight: 700;
        color: #64748b;
        font-size: 0.95rem;
    }

    /* --- Estados (Carga / Animaciones) --- */
    &__estado {
        text-align: center;
        padding: 60px 0;

        p {
            color: #64748b;
            font-weight: 600;
            margin-top: 15px;
        }
    }

    &__spinner {
        display: inline-block;
        width: 40px;
        height: 40px;
        border: 4px solid rgba(var(--main-color-rgb, 217, 106, 26), 0.2);
        border-top-color: var(--main-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    }
}

/* Animaciones Globales */
.fade-in {
    animation: fadeIn 0.4s ease-out;

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
}

.lista-enter-active,
.lista-leave-active {
    transition: all 0.3s ease;
}

.lista-enter-from,
.lista-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

/* --- Media Queries --- */
@media (max-width: 768px) {
    .solicitud {
        &__titulo {
            font-size: 2rem;
        }

        &__cuadricula {
            grid-template-columns: 1fr;
        }

        &__paginacion {
            flex-direction: column;
            gap: 15px;
            width: 100%;

            .solicitud__btn-pagina {
                width: 100%;
                justify-content: center;
            }
        }
    }
}
</style>