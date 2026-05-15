<script setup>
import { ref } from 'vue'

const codigoBusqueda = ref('')
const pedido = ref(null)
const cargando = ref(false)
const errorMensaje = ref('')

const buscarPedido = async () => {
    if (!codigoBusqueda.value.trim()) {
        errorMensaje.value = 'Por favor, introduce un código de seguimiento válido.'
        return
    }

    // Limpiamos estados anteriores
    cargando.value = true
    errorMensaje.value = ''
    pedido.value = null

    // Aseguramos que el código tenga el formato correcto (mayúsculas y sin espacios extra)
    const codigoLimpio = codigoBusqueda.value.trim().toUpperCase()

    try {
        // NOTA: Esta ruta la crearemos en el backend en el siguiente paso
        const res = await fetch(`http://127.0.0.1:8000/api/pedidos/seguimiento/${codigoLimpio}`, {
            headers: { 'Accept': 'application/json' }
        })

        if (res.ok) {
            pedido.value = await res.json()
        } else {
            errorMensaje.value = 'No hemos encontrado ningún pedido con ese código. Revisa que esté bien escrito (Ej: JT-A1B2C3).'
        }
    } catch (error) {
        errorMensaje.value = 'Error de conexión con el servidor. Inténtalo más tarde.'
        console.error(error)
    } finally {
        cargando.value = false
    }
}

// Lógica para la línea de tiempo visual
const pasosEstado = ['Pendiente', 'En Taller', 'Reparado', 'Entregado']

const obtenerNivelEstado = (estadoActual) => {
    if (estadoActual === 'Cancelado') return -1;
    return pasosEstado.indexOf(estadoActual);
}
</script>

<template>
    <main class="consulta-container">
        <div class="consulta-header">
            <div class="icon-box">
                <i class="fa-solid fa-magnifying-glass-location"></i>
            </div>
            <h1>Rastrea tu Reparación</h1>
            <p>Introduce el código de seguimiento de 8 caracteres que recibiste al confirmar la orden para conocer el
                estado en tiempo real de tu dispositivo.</p>
        </div>

        <div class="search-section">
            <form @submit.prevent="buscarPedido" class="search-form">
                <div class="input-wrapper">
                    <i class="fa-solid fa-barcode icon-left"></i>
                    <input type="text" v-model="codigoBusqueda" placeholder="Ej: JT-X9F2M1" class="track-input"
                        autocomplete="off">
                    <button type="submit" class="btn-track" :disabled="cargando">
                        <span v-if="cargando" class="spinner-mini"></span>
                        <span v-else>Localizar <i class="fa-solid fa-arrow-right"></i></span>
                    </button>
                </div>
            </form>

            <transition name="fade">
                <div v-if="errorMensaje" class="error-alert">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ errorMensaje }}
                </div>
            </transition>
        </div>

        <transition name="slide-up">
            <div v-if="pedido" class="result-card">

                <div class="result-header">
                    <div class="header-info">
                        <span class="label">Código de Orden</span>
                        <h2 class="tracking-code">{{ pedido.codigo_seguimiento }}</h2>
                    </div>
                    <div class="header-date">
                        <span class="label">Fecha de Entrada</span>
                        <strong>{{ new Date(pedido.created_at).toLocaleDateString('es-ES') }}</strong>
                    </div>
                </div>

                <div class="timeline-container" v-if="pedido.estado !== 'Cancelado'">
                    <div v-for="(paso, index) in pasosEstado" :key="index" class="timeline-step" :class="{
                        'active': index <= obtenerNivelEstado(pedido.estado),
                        'current': index === obtenerNivelEstado(pedido.estado)
                    }">
                        <div class="step-icon">
                            <i class="fa-solid fa-check" v-if="index < obtenerNivelEstado(pedido.estado)"></i>
                            <i class="fa-solid" :class="{
                                'fa-box': index === 0,
                                'fa-screwdriver-wrench': index === 1,
                                'fa-check-double': index === 2,
                                'fa-handshake': index === 3
                            }" v-else></i>
                        </div>
                        <span class="step-label">{{ paso }}</span>
                        <div class="step-line" v-if="index < pasosEstado.length - 1"></div>
                    </div>
                </div>

                <div v-else class="cancelled-banner">
                    <i class="fa-solid fa-ban"></i>
                    <div>
                        <h3>Orden Cancelada</h3>
                        <p>Esta reparación ha sido anulada. Si crees que es un error, contacta con el taller.</p>
                    </div>
                </div>

                <div class="details-grid">
                    <div class="detail-box">
                        <i class="fa-solid fa-mobile-screen text-main"></i>
                        <div class="text-info">
                            <span class="label">Dispositivo</span>
                            <strong>{{ pedido.dispositivo?.marca }} {{ pedido.dispositivo?.modelo }}</strong>
                        </div>
                    </div>

                    <div class="detail-box">
                        <i class="fa-solid fa-list-check text-main"></i>
                        <div class="text-info">
                            <span class="label">Intervención Solicitada</span>
                            <strong>{{ pedido.tipo_reparacion }}</strong>
                        </div>
                    </div>

                    <div class="detail-box total-box">
                        <i class="fa-solid fa-receipt text-main"></i>
                        <div class="text-info">
                            <span class="label">Presupuesto Estimado</span>
                            <strong class="price">{{ pedido.precio_estimado }}</strong>
                        </div>
                    </div>
                </div>

            </div>
        </transition>
    </main>
</template>

<style scoped lang="scss">
.consulta-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 60px 20px;
    min-height: 70vh;
    font-family: 'Inter', system-ui, sans-serif;
    color: var(--text-color);
}

/* === CABECERA === */
.consulta-header {
    text-align: center;
    margin-bottom: 40px;

    .icon-box {
        width: 80px;
        height: 80px;
        background-color: rgba(var(--main-color-rgb, 217, 106, 26), 0.1);
        color: var(--main-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto 20px auto;
    }

    h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0 0 10px 0;
        color: #0f172a;
    }

    p {
        color: #64748b;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.5;
    }
}

/* === BUSCADOR === */
.search-section {
    margin-bottom: 40px;
}

.input-wrapper {
    display: flex;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    border-radius: 16px;
    background: white;
    padding: 8px;
    border: 2px solid #e2e8f0;
    transition: border-color 0.3s;

    &:focus-within {
        border-color: var(--main-color);
    }

    .icon-left {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.2rem;
    }

    .track-input {
        flex-grow: 1;
        border: none;
        outline: none;
        padding: 15px 15px 15px 50px;
        font-size: 1.2rem;
        font-weight: 700;
        color: #0f172a;
        background: transparent;
        text-transform: uppercase;

        &::placeholder {
            color: #cbd5e1;
            font-weight: 500;
            text-transform: none;
        }
    }

    .btn-track {
        background-color: var(--main-color);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0 30px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;

        &:hover:not(:disabled) {
            background-color: var(--secondary-color, #b55612);
            transform: translateX(-2px);
        }

        &:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    }
}

.error-alert {
    margin-top: 15px;
    padding: 15px;
    background-color: #fef2f2;
    border: 1px solid #f87171;
    color: #b91c1c;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* === TARJETA DE RESULTADO === */
.result-card {
    background: white;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.result-header {
    background: #f8fafc;
    padding: 25px 30px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;

    .label {
        display: block;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .tracking-code {
        margin: 0;
        font-size: 2rem;
        font-weight: 900;
        color: var(--main-color);
        letter-spacing: 1px;
    }

    .header-date strong {
        font-size: 1.2rem;
        color: #0f172a;
    }
}

/* TIMELINE MAGIA */
.timeline-container {
    display: flex;
    justify-content: space-between;
    padding: 40px 30px;
    position: relative;
}

.step-line {
    position: absolute;
    top: 65px;
    left: 10%;
    right: 10%;
    height: 4px;
    background-color: #e2e8f0;
    z-index: 1;
    border-radius: 2px;
}

.timeline-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
    width: 25%;

    .step-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: white;
        border: 4px solid #e2e8f0;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 15px;
        transition: all 0.4s ease;
    }

    .step-label {
        font-weight: 700;
        color: #94a3b8;
        font-size: 0.9rem;
        text-align: center;
        transition: 0.4s;
    }

    /* Estado Activo (Pasado) */
    &.active {
        .step-icon {
            border-color: var(--main-color);
            background-color: var(--main-color);
            color: white;
        }

        .step-label {
            color: #0f172a;
        }

        /* Colorea la línea conectora hacia el siguiente paso */
        &::after {
            content: '';
            position: absolute;
            top: 25px;
            left: 50%;
            width: 100%;
            height: 4px;
            background-color: var(--main-color);
            z-index: -1;
        }
    }

    /* El último item activo no colorea la línea hacia adelante */
    &:last-child::after {
        display: none;
    }

    &.current::after {
        background-color: #e2e8f0;
    }

    /* Si es el actual, el camino futuro sigue gris */

    /* Estado Actual (Parpadeo suave) */
    &.current .step-icon {
        box-shadow: 0 0 0 8px rgba(var(--main-color-rgb, 217, 106, 26), 0.2);
        animation: pulse 2s infinite;
    }
}

.cancelled-banner {
    background: #fef2f2;
    padding: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    color: #991b1b;

    i {
        font-size: 3rem;
        color: #ef4444;
    }

    h3 {
        margin: 0 0 5px 0;
        font-size: 1.3rem;
    }

    p {
        margin: 0;
        font-size: 0.95rem;
    }
}

/* Detalles Inferiores */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    padding: 30px;
    background: white;
    border-top: 1px solid #f1f5f9;
}

.detail-box {
    display: flex;
    align-items: flex-start;
    gap: 15px;

    .text-main {
        font-size: 1.5rem;
        color: #94a3b8;
        margin-top: 5px;
    }

    .label {
        display: block;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 4px;
    }

    strong {
        display: block;
        font-size: 1.05rem;
        color: #0f172a;
        line-height: 1.3;
    }
}

.total-box {
    background: #f8fafc;
    padding: 15px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;

    .text-main {
        color: var(--main-color);
    }

    .price {
        font-size: 1.4rem;
        color: var(--main-color);
        font-weight: 900;
    }
}

/* Animaciones */
.spinner-mini {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(var(--main-color-rgb, 217, 106, 26), 0.4);
    }

    70% {
        box-shadow: 0 0 0 10px rgba(var(--main-color-rgb, 217, 106, 26), 0);
    }

    100% {
        box-shadow: 0 0 0 0 rgba(var(--main-color-rgb, 217, 106, 26), 0);
    }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-up-enter-from,
.slide-up-leave-to {
    opacity: 0;
    transform: translateY(20px);
}

@media (max-width: 768px) {
    .input-wrapper {
        flex-direction: column;
        padding: 15px;
        gap: 15px;

        .icon-left {
            top: 35px;
        }

        .btn-track {
            padding: 15px;
            justify-content: center;
        }
    }

    .timeline-container {
        flex-direction: column;
        gap: 30px;
        align-items: flex-start;
        padding-left: 50px;
    }

    .step-line {
        width: 4px;
        height: calc(100% - 100px);
        top: 50px;
        left: 74px;
    }

    .timeline-step {
        flex-direction: row;
        width: 100%;
        gap: 20px;

        .step-icon {
            margin-bottom: 0;
        }

        .step-label {
            text-align: left;
            font-size: 1.1rem;
        }

        &.active::after {
            width: 4px;
            height: 100%;
            top: 50px;
            left: 24px;
        }
    }
}
</style>