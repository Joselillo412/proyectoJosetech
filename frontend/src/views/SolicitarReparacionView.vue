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
    <main class="solicitud-container fade-in">
        <div class="wizard-header">
            <span class="step-badge">Paso 1 de 2</span>
            <h1>¿Qué equipo necesitas reparar?</h1>
            <p>Busca tu modelo en nuestro catálogo o escríbelo si no lo encuentras en la lista.</p>
        </div>

        <div class="search-box">
            <div class="input-wrapper">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" v-model="busqueda" placeholder="Ej: iPhone 13 Pro, Samsung S23..."
                    class="device-input" autofocus>
                <button v-if="busqueda" @click="busqueda = ''" class="clear-btn">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <div v-if="cargando" class="loading-state">
            <span class="spinner"></span>
            <p>Cargando catálogo...</p>
        </div>

        <div v-else class="results-container">

            <transition-group name="list" tag="div" class="device-grid">
                <div v-for="disp in dispositivosPaginados" :key="disp.id" class="device-card"
                    @click="seleccionarDispositivo(disp)">
                    <div class="device-img-wrapper">
                        <img v-if="disp.imagen_url" :src="disp.imagen_url" :alt="disp.modelo">
                        <i v-else class="fa-solid fa-mobile-screen placeholder-icon"></i>
                    </div>
                    <div class="device-info">
                        <span class="brand">{{ disp.marca }}</span>
                        <h3 class="model">{{ disp.modelo }}</h3>
                    </div>
                    <div class="arrow-icon">
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                </div>
            </transition-group>

            <div class="pagination-controls" v-if="totalPaginas > 1 && dispositivosPaginados.length > 0">
                <button @click="cambiarPagina(-1)" :disabled="paginaActual === 1" class="btn-page">
                    <i class="fa-solid fa-chevron-left"></i> Anterior
                </button>
                <span class="page-info">Página {{ paginaActual }} de {{ totalPaginas }}</span>
                <button @click="cambiarPagina(1)" :disabled="paginaActual === totalPaginas" class="btn-page">
                    Siguiente <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            <div v-if="dispositivosFiltrados.length === 0 && busqueda.trim() !== ''" class="manual-entry-card fade-in">
                <div class="icon-box">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div class="text-box">
                    <h3>No encontramos "{{ busqueda }}"</h3>
                    <p>¡No pasa nada! Haz clic en continuar y cuéntanos qué le pasa a tu equipo.</p>
                </div>
                <button @click="continuarManual" class="btn-manual">
                    Continuar con este equipo <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </main>
</template>

<style scoped lang="scss">
/* (MANTENEMOS TUS ESTILOS ORIGINALES Y AÑADIMOS LA PAGINACIÓN) */
.solicitud-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 40px 20px;
    min-height: 70vh;
}

.wizard-header {
    text-align: center;
    margin-bottom: 40px;

    .step-badge {
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

    h1 {
        font-size: 2.5rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 10px 0;
    }

    p {
        color: #64748b;
        font-size: 1.1rem;
    }
}

/* BUSCADOR */
.search-box {
    margin-bottom: 30px;

    .input-wrapper {
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

    .search-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.2rem;
    }

    .device-input {
        width: 100%;
        padding: 20px 50px;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        font-size: 1.1rem;
        font-weight: 600;
        outline: none;
        transition: border-color 0.3s;
        color: #0f172a;

        &:focus {
            border-color: var(--main-color);
        }

        &::placeholder {
            color: #cbd5e1;
            font-weight: 500;
        }
    }

    .clear-btn {
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

        &:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
    }
}

/* GRILLA DE DISPOSITIVOS */
.device-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.device-card {
    background: white;
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

        .arrow-icon {
            color: var(--main-color);
            transform: translateX(3px);
        }
    }

    .device-img-wrapper {
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

        .placeholder-icon {
            font-size: 1.8rem;
            color: #cbd5e1;
        }
    }

    .device-info {
        flex-grow: 1;

        .brand {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
        }

        .model {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
        }
    }

    .arrow-icon {
        color: #cbd5e1;
        transition: all 0.3s;
    }
}

/* ENTRADA MANUAL */
.manual-entry-card {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    padding: 30px;
    text-align: center;
    margin-top: 20px;

    .icon-box {
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

    .text-box {
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

    .btn-manual {
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
        transition: 0.2s;

        &:hover {
            background: var(--secondary-color, #b55612);
            transform: translateY(-2px);
        }
    }
}

/* PAGINACIÓN */
.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f1f5f9;

    .btn-page {
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

        &:hover:not(:disabled) {
            border-color: var(--main-color);
            color: var(--main-color);
            box-shadow: 0 4px 10px rgba(var(--main-color-rgb, 217, 106, 26), 0.1);
        }

        &:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8fafc;
        }
    }

    .page-info {
        font-weight: 700;
        color: #64748b;
        font-size: 0.95rem;
    }
}

/* ESTADOS Y ANIMACIONES */
.loading-state {
    text-align: center;
    padding: 60px 0;

    p {
        color: #64748b;
        font-weight: 600;
        margin-top: 15px;
    }
}

.spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 4px solid rgba(var(--main-color-rgb, 217, 106, 26), 0.2);
    border-top-color: var(--main-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.fade-in {
    animation: fadeIn 0.4s ease-out;
}

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

.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

@media (max-width: 768px) {
    .wizard-header h1 {
        font-size: 2rem;
    }

    .device-grid {
        grid-template-columns: 1fr;
    }

    .pagination-controls {
        flex-direction: column;
        gap: 15px;
        width: 100%;

        .btn-page {
            width: 100%;
            justify-content: center;
        }
    }
}
</style>