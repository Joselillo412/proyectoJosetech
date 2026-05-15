<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const dispositivoId = route.params.id

// Variables de estado del servidor
const dispositivo = ref(null)
const ifixitInfo = ref(null)
const icecatInfo = ref(null)
const cargando = ref(true)
const error = ref(false)

// --- LÓGICA DE SELECCIÓN MULTI-AVERÍA ---
// Catálogo completo de reparaciones Josetech (Precios Base Aprox.)
const serviciosCat = [
  { id: 'bat', nombre: 'Cambio de Batería', precio: 60, icono: 'fa-solid fa-battery-half' },
  { id: 'pan', nombre: 'Cambio de Pantalla', precio: 90, extraNota: '60€-120€ según el tipo de pantalla', icono: 'fa-solid fa-mobile-screen' },
  { id: 'tapa', nombre: 'Cristal Trasero', precio: 55, icono: 'fa-solid fa-clone' },
  { id: 'carga', nombre: 'Puerto de Carga', precio: 50, icono: 'fa-solid fa-plug' },
  { id: 'camT', nombre: 'Cámara Trasera', precio: 70, icono: 'fa-solid fa-camera' },
  { id: 'camF', nombre: 'Cámara Frontal', precio: 50, icono: 'fa-solid fa-camera-rotate' },
  { id: 'bot', nombre: 'Botones Encendido/Volumen', precio: 50, icono: 'fa-solid fa-toggle-on' },
  { id: 'alt', nombre: 'Altavoz Principal', precio: 20, icono: 'fa-solid fa-volume-high' },
  { id: 'aur', nombre: 'Auricular Llamadas', precio: 20, icono: 'fa-solid fa-phone-volume' }
]

// Array que guarda los IDs de los servicios seleccionados por el cliente
const serviciosSeleccionados = ref(['pan']) // Dejamos Pantalla pre-marcada por defecto

// Formulario final
const descripcionProblema = ref('')
const enviando = ref(false)
const mensajeExito = ref(false)
const errorEnvio = ref('')

// Alternar selección (Añadir/Quitar del carrito de averías)
const toggleServicio = (id) => {
  const index = serviciosSeleccionados.value.indexOf(id)
  if (index > -1) {
    // Si ya está, lo quitamos (asegurando que al menos quede uno, o permitiendo vaciarlo)
    serviciosSeleccionados.value.splice(index, 1)
  } else {
    // Si no está, lo añadimos
    serviciosSeleccionados.value.push(id)
  }
}

// Comprueba si un servicio está marcado para aplicarle el CSS naranja
const estaSeleccionado = (id) => serviciosSeleccionados.value.includes(id)

// Cálculo reactivo del precio total estimado
const sumaTotalEstimada = computed(() => {
  return serviciosSeleccionados.value.reduce((total, id) => {
    const serv = serviciosCat.find(s => s.id === id)
    return total + (serv ? serv.precio : 0)
  }, 0)
})

// Devuelve los nombres literales seleccionados para mandarlos a la base de datos
const resumenNombresSeleccionados = computed(() => {
  return serviciosSeleccionados.value
    .map(id => serviciosCat.find(s => s.id === id)?.nombre)
    .join(', ')
})

const formatearFecha = (f) => f ? new Date(f).toLocaleDateString('es-ES') : 'N/A';

// Cargar información del Backend
onMounted(async () => {
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/dispositivos/${dispositivoId}`)
    if (!res.ok) throw new Error()
    const data = await res.json()

    dispositivo.value = data.dispositivo
    ifixitInfo.value = data.api_ifixit
    icecatInfo.value = data.api_icecat
  } catch (e) {
    error.value = true
  } finally {
    cargando.value = false
  }
})

// Enviar el pedido compuesto al Backend
const enviarSolicitud = async () => {
  if (serviciosSeleccionados.value.length === 0) {
    alert('Por favor, selecciona al menos una reparación para calcular el presupuesto.')
    return
  }

  const token = localStorage.getItem('auth_token')
  if (!token) {
    alert('Debes iniciar sesión o registrarte para solicitar una recogida gratuita.')
    router.push('/login')
    return
  }

  enviando.value = true
  errorEnvio.value = ''
  mensajeExito.value = false

  try {
    const respuesta = await fetch('http://127.0.0.1:8000/api/pedidos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        dispositivo_id: dispositivoId,
        // Mandamos la cadena unida (Ej: "Cambio de Pantalla, Cambio de Batería")
        tipo_reparacion: resumenNombresSeleccionados.value,
        descripcion: descripcionProblema.value || 'Sin detalles adicionales',
        // Mandamos el string con el precio combinado calculado
        precio_estimado: `${sumaTotalEstimada.value}€ Aprox.`
      })
    })

    if (respuesta.ok) {
      mensajeExito.value = true
      descripcionProblema.value = ''
      serviciosSeleccionados.value = [] // Limpiamos selección
    } else {
      const datos = await respuesta.json()
      errorEnvio.value = datos.message || 'Error al procesar la solicitud.'
    }
  } catch (e) {
    errorEnvio.value = 'Error de conexión con el servidor.'
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="presupuesto-wrapper">
    <div v-if="cargando" class="state-msg">
      <div class="spinner"></div>
      <p>Sincronizando con bases de datos técnicas de Josetech...</p>
    </div>

    <div v-else-if="error" class="state-msg error">
      <i class="fa-solid fa-circle-exclamation"></i>
      <p>No se pudo obtener la información del dispositivo.</p>
      <router-link to="/" class="btn-action">Volver al buscador</router-link>
    </div>

    <div v-else class="content fade-in">

      <header class="device-header">
        <div class="device-image-box">
          <img v-if="dispositivo.imagen_url" :src="dispositivo.imagen_url" :alt="dispositivo.modelo" class="device-img">
          <i v-else class="fa-solid fa-mobile-screen-button placeholder-icon"></i>
        </div>
        <div class="device-titles">
          <span class="category-tag">{{ dispositivo.tipo }}</span>
          <h1>{{ dispositivo.marca }} <span>{{ dispositivo.modelo }}</span></h1>
          <p>Selecciona una o varias reparaciones para combinar tu presupuesto</p>
        </div>
      </header>

      <section class="tarifas-section">
        <div class="section-title-bar">
          <h2>Servicios Disponibles <span class="badge-inc">Mano de obra incluida</span></h2>
          <span class="instruction-text">Puedes marcar varios a la vez</span>
        </div>

        <div class="tarifas-grid">
          <div v-for="servicio in serviciosCat" :key="servicio.id" class="tarifa-card"
            :class="{ active: estaSeleccionado(servicio.id) }" @click="toggleServicio(servicio.id)">
            <div class="checkbox-indicator">
              <i v-if="estaSeleccionado(servicio.id)" class="fa-solid fa-circle-check"></i>
              <span v-else class="circle-empty"></span>
            </div>

            <div class="tarifa-icon"><i :class="servicio.icono"></i></div>

            <div class="tarifa-info">
              <h3>{{ servicio.nombre }}</h3>
              <p v-if="servicio.extraNota" class="sub-note">{{ servicio.extraNota }}</p>
            </div>

            <div class="tarifa-precio">
              {{ servicio.precio }}€ <span>Aprox.</span>
            </div>
          </div>
        </div>

        <div class="resumen-bar" v-if="serviciosSeleccionados.length > 0">
          <div class="resumen-info">
            <span class="resumen-label">Reparaciones marcadas ({{ serviciosSeleccionados.length }}):</span>
            <span class="resumen-list">{{ resumenNombresSeleccionados }}</span>
          </div>
          <div class="resumen-total">
            <span>Total Estimado:</span>
            <strong>{{ sumaTotalEstimada }}€</strong>
          </div>
        </div>
      </section>

      <div class="grid-layout">
        <section v-if="ifixitInfo" class="card info-card">
          <div class="card-head orange">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            <h3>Análisis de Dificultad iFixit</h3>
          </div>
          <div class="card-body">
            <div class="data-row">
              <span>Dificultad original:</span>
              <span class="pill" :class="ifixitInfo.dificultad_original.toLowerCase().replace(' ', '-')">
                {{ ifixitInfo.dificultad_original }}
              </span>
            </div>
            <p v-if="ifixitInfo.coste_extra_mano_obra > 0" class="note">
              * Este modelo presenta sellado adhesivo complejo de destapar.
            <br /><br />
              * Este modelo prensenta problemas de acceso a componentes internos.
            </p>
          </div>
        </section>

        <section v-if="icecatInfo" class="card info-card">
          <div class="card-head blue">
            <i class="fa-solid fa-microchip"></i>
            <h3>Disponibilidad Icecat</h3>
          </div>
          <div class="card-body">
            <div class="data-row">
              <span>Fecha de lanzamiento:</span>
              <strong>{{ formatearFecha(icecatInfo.lanzamiento) }}</strong>
            </div>
            <div v-if="icecatInfo.aviso_obsolescencia" class="warning-box">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <span>Hardware con baja disponibilidad en almacenes.</span>
            </div>
          </div>
        </section>
      </div>

      <section class="contact-section">
        <div class="form-header">
          <h3>Solicitar Recogida Gratuita en Domicilio</h3>
          <p>Un mensajero recogerá tu equipo en el acto. Al procesar el alta confirmas las reparaciones estimadas.</p>
        </div>

        <div v-if="mensajeExito" class="alert-success">
          <i class="fa-solid fa-circle-check"></i>
          <div>
            <h4>¡Solicitud enviada con éxito!</h4>
            <p>Hemos registrado tu orden combinada en nuestro taller. Te contactaremos por teléfono para coordinar el
              transporte.</p>
          </div>
        </div>

        <div v-if="errorEnvio" class="alert-error">
          <i class="fa-solid fa-triangle-exclamation"></i> {{ errorEnvio }}
        </div>

        <form @submit.prevent="enviarSolicitud" v-if="!mensajeExito">
          <label for="desc">¿Presenta algún otro fallo o síntoma el equipo? Cuentanos que le sucedió al equipo</label>
          <textarea id="desc" v-model="descripcionProblema"
            placeholder="Añade observaciones adicionales sobre el estado de la carcasa, si ha sufrido daños por agua, bloqueos, etc."></textarea>

          <button type="submit" class="btn-submit" :disabled="enviando || serviciosSeleccionados.length === 0">
            <span v-if="enviando" class="spinner-btn"></span>
            <span v-else>Confirmar Orden de Reparación ({{ sumaTotalEstimada }}€) <i
                class="fa-solid fa-truck-fast"></i></span>
          </button>
        </form>

        <div v-if="mensajeExito" class="actions-footer">
          <router-link to="/" class="btn-secondary">Volver al Buscador Principal</router-link>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped lang="scss">
.presupuesto-wrapper {
  max-width: 1100px;
  margin: 0 auto;
  padding: 40px 20px;
  min-height: 80vh;
  font-family: 'Inter', system-ui, sans-serif;
  color: var(--text-color);
}

/* === CABECERA === */
.device-header {
  display: flex;
  align-items: center;
  gap: 30px;
  background: white;
  padding: 30px;
  border-radius: 20px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
  margin-bottom: 40px;
}

.device-image-box {
  width: 120px;
  height: 120px;
  background-color: #f8fafc;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  padding: 10px;
}

.device-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.placeholder-icon {
  font-size: 4rem;
  color: #cbd5e1;
}

.device-titles {
  h1 {
    font-size: 2.2rem;
    margin: 8px 0;
    font-weight: 800;

    span {
      color: var(--main-color);
    }
  }

  p {
    color: #64748b;
    margin: 0;
    font-size: 1rem;
  }
}

.category-tag {
  background: #f1f5f9;
  color: #475569;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
}

/* === GRID DE SERVICIOS MÚLTIPLES === */
.tarifas-section {
  margin-bottom: 40px;
}

.section-title-bar {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 10px;

  h2 {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .instruction-text {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 600;
  }
}

.badge-inc {
  background-color: rgba(var(--main-color-rgb, 217, 106, 26), 0.1);
  color: var(--main-color);
  font-size: 0.8rem;
  padding: 4px 12px;
  border-radius: 12px;
  font-weight: 700;
  text-transform: uppercase;
}

.tarifas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 18px;
  margin-bottom: 25px;
}

.tarifa-card {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 16px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  /* Evita que el usuario arrastre la tarjeta al hacer clics rápidos */
  user-select: none;

  &:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
  }

  &.active {
    border-color: var(--main-color);
    background-color: rgba(var(--main-color-rgb, 217, 106, 26), 0.03);
    box-shadow: 0 10px 20px rgba(var(--main-color-rgb, 217, 106, 26), 0.08);

    .tarifa-icon {
      background-color: var(--main-color);
      color: white;
    }

    .tarifa-precio {
      color: var(--main-color);
    }
  }
}

/* Checkbox esquinero */
.checkbox-indicator {
  position: absolute;
  top: 15px;
  right: 15px;
  font-size: 1.3rem;

  i {
    color: var(--main-color);
  }

  .circle-empty {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #cbd5e1;
    border-radius: 50%;
  }
}

.tarifa-icon {
  width: 42px;
  height: 42px;
  background-color: #f1f5f9;
  color: #64748b;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  margin-bottom: 12px;
  transition: all 0.2s ease;
}

.tarifa-info {
  margin-bottom: 12px;

  h3 {
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0 0 4px 0;
    color: var(--text-color);
  }

  .sub-note {
    font-size: 0.75rem;
    color: #94a3b8;
    margin: 0;
    font-weight: 600;
  }
}

.tarifa-precio {
  font-size: 1.3rem;
  font-weight: 800;
  color: #0f172a;
  border-top: 1px solid #f1f5f9;
  padding-top: 10px;
  transition: color 0.2s;

  span {
    font-size: 0.8rem;
    font-weight: 600;
    color: #94a3b8;
  }
}

/* BARRA FLOTANTE DE RESUMEN DE PRECIO */
.resumen-bar {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: white;
  padding: 20px 25px;
  border-radius: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
  animation: fadeInUp 0.3s ease;
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);

  .resumen-info {
    flex-grow: 1;
    max-width: 70%;

    .resumen-label {
      color: #94a3b8;
      font-size: 0.85rem;
      display: block;
      margin-bottom: 3px;
    }

    .resumen-list {
      font-weight: 600;
      font-size: 1rem;
      color: white;
      line-height: 1.3;
      display: block;
    }
  }

  .resumen-total {
    text-align: right;

    span {
      color: #94a3b8;
      font-size: 0.85rem;
      display: block;
    }

    strong {
      font-size: 1.8rem;
      color: var(--main-color);
      font-weight: 900;
    }
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(15px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* === APIS EXTRA === */
.grid-layout {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
  overflow: hidden;
  border: 1px solid #f1f5f9;
}

.card-head {
  padding: 16px 20px;
  color: white;
  display: flex;
  align-items: center;
  gap: 10px;

  h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
  }

  &.orange {
    background: #f97316;
  }

  &.blue {
    background: #3b82f6;
  }
}

.card-body {
  padding: 20px;
}

.data-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.95rem;
}

.pill {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;

  &.easy {
    background: #dcfce7;
    color: #166534;
  }

  &.moderate {
    background: #fef9c3;
    color: #854d0e;
  }

  &.difficult {
    background: #fee2e2;
    color: #991b1b;
  }
}

.note {
  font-size: 0.85rem;
  color: #64748b;
  margin-top: 12px;
}

.warning-box {
  margin-top: 15px;
  background: #fff7ed;
  border: 1px solid #fed7aa;
  padding: 12px;
  border-radius: 10px;
  color: #9a3412;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* === FORMULARIO === */
.contact-section {
  background: white;
  padding: 35px;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);

  .form-header {
    margin-bottom: 25px;

    h3 {
      font-size: 1.4rem;
      font-weight: 800;
      margin: 0 0 5px 0;
    }

    p {
      color: #64748b;
      font-size: 0.95rem;
      margin: 0;
    }
  }

  label {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-color);
  }
}

textarea {
  width: 100%;
  height: 110px;
  padding: 15px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  margin: 10px 0 20px 0;
  font-family: inherit;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s;
  resize: vertical;

  &:focus {
    border-color: var(--main-color);
  }
}

.btn-submit {
  background: var(--main-color);
  color: white;
  border: none;
  padding: 16px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  transition: all 0.2s;

  &:hover:not(:disabled) {
    background: var(--secondary-color, #b55612);
    transform: translateY(-2px);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

/* Alertas */
.alert-success {
  background-color: #f0fdf4;
  border: 1px solid #bbf7d0;
  padding: 20px;
  border-radius: 16px;
  display: flex;
  gap: 15px;
  align-items: flex-start;
  color: #166534;

  i {
    font-size: 1.8rem;
    color: #22c55e;
    margin-top: 2px;
  }

  h4 {
    margin: 0 0 5px 0;
    font-size: 1.1rem;
    font-weight: 700;
  }

  p {
    margin: 0;
    font-size: 0.95rem;
    color: #15803d;
    line-height: 1.4;
  }
}

.alert-error {
  background-color: #fef2f2;
  border: 1px solid #fee2e2;
  color: #991b1b;
  padding: 15px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

.actions-footer {
  margin-top: 20px;
  text-align: center;
}

.btn-secondary {
  color: var(--main-color);
  font-weight: 700;
  text-decoration: none;
}

.fade-in {
  animation: fadeIn 0.4s ease-in;
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

.state-msg {
  text-align: center;
  padding: 100px 0;
  color: #64748b;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f1f5f9;
  border-top-color: var(--main-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

.spinner-btn {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@media (max-width: 768px) {
  .device-header {
    flex-direction: column;
    text-align: center;
    gap: 15px;
    padding: 20px;
  }

  .device-image-box {
    width: 100px;
    height: 100px;
  }

  .resumen-bar {
    flex-direction: column;
    text-align: center;

    .resumen-info {
      max-width: 100%;
      margin-bottom: 10px;
    }

    .resumen-total {
      text-align: center;
    }
  }
}
</style>