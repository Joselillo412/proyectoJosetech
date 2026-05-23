<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const parametroId = route.params.id
const esCustom = parametroId === 'custom'
const modeloPersonalizado = route.query.modelo || ''
const ID_DISPOSITIVO_GENERICO = 1

const dispositivo = ref(null)
const ifixitInfo = ref(null)
const icecatInfo = ref(null)
const cargando = ref(true)
const error = ref(false)

const serviciosCat = ref([])
const serviciosSeleccionados = ref([])

const descripcionProblema = ref('')
const enviando = ref(false)
const mensajeExito = ref(false)
const errorEnvio = ref('')
const codigoSeguimientoGenerado = ref('')

const toggleServicio = (id) => {
  const index = serviciosSeleccionados.value.indexOf(id)
  if (index > -1) {
    serviciosSeleccionados.value.splice(index, 1)
  } else {
    serviciosSeleccionados.value.push(id)
  }
}

const estaSeleccionado = (id) => serviciosSeleccionados.value.includes(id)

const serviciosFiltrados = computed(() => {
  if (!dispositivo.value || serviciosCat.value.length === 0) return []

  // Normalizamos: quitamos espacios, acentos y pasamos a minúsculas
  const tipo = dispositivo.value.tipo
    ? dispositivo.value.tipo.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim()
    : '';

  let categoriaFiltro = 'moviles';

  if (tipo.includes('consola')) {
    categoriaFiltro = 'consolas';
  } else if (tipo.includes('ordenador') || tipo.includes('portatil') || tipo.includes('pc')) {
    categoriaFiltro = 'ordenadores';
  } else {
    categoriaFiltro = 'moviles';
  }

  // Debug: Esto te ayudará a saber por qué falla en consola
  console.log("Tipo detectado:", tipo, "-> Categoría filtrada:", categoriaFiltro);

  return serviciosCat.value.filter(s => s.categoria === categoriaFiltro);
})
const sumaTotalEstimada = computed(() => {
  return serviciosSeleccionados.value.reduce((total, id) => {
    const serv = serviciosCat.value.find(s => s.id === id)
    return total + (serv ? Number(serv.precio) : 0)
  }, 0)
})

const resumenNombresSeleccionados = computed(() => {
  return serviciosSeleccionados.value
    .map(id => serviciosCat.value.find(s => s.id === id)?.nombre)
    .join(', ')
})

const formatearFecha = (f) => f ? new Date(f).toLocaleDateString('es-ES') : 'N/A';

onMounted(async () => {
  // 1. CARGAMOS LA INFO DEL DISPOSITIVO
  if (esCustom) {
    const tipoRecibido = route.query.tipo;

    dispositivo.value = {
      id: ID_DISPOSITIVO_GENERICO,
      marca: tipoRecibido === 'portatil' ? 'Portatil' : 'Dispositivo no catalogado',
      modelo: modeloPersonalizado || (tipoRecibido === 'portatil' ? 'Portátil a reparar' : 'Equipo sin especificar'),
      // Aquí establecemos 'Portátil' para que el filtro lo detecte
      tipo: tipoRecibido === 'portatil' ? 'Portátil' : 'Otros',
      imagen_url: tipoRecibido === 'portatil'
        ? 'https://www.sociallovers.cat/wp-content/uploads/2019/10/Alquiler-ordenador-portatil.png'
        : ''
    }
  } else {
    // ... tu lógica de fetch para dispositivos existentes ...
    try {
      const res = await fetch(`https://proyectojosetech.onrender.com/api/dispositivos/${parametroId}`)
      if (!res.ok) throw new Error()
      const data = await res.json()
      dispositivo.value = data.dispositivo
      ifixitInfo.value = data.api_ifixit
      icecatInfo.value = data.api_icecat
    } catch (e) {
      error.value = true
    }
  }

  // 2. CARGAMOS LOS SERVICIOS DE LA BASE DE DATOS
  try {
    const resServicios = await fetch('https://proyectojosetech.onrender.com/api/servicios')
    if (resServicios.ok) {
      serviciosCat.value = await resServicios.json()

      // Auto-seleccionar el primer servicio de la lista filtrada
      if (serviciosFiltrados.value.length > 0) {
        serviciosSeleccionados.value.push(serviciosFiltrados.value[0].id)
      }
    }
  } catch (e) {
    console.error("Error cargando servicios dinámicos", e)
  }

  cargando.value = false
})
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
  codigoSeguimientoGenerado.value = ''

  const descripcionFinal = esCustom
    ? `[MODELO CUSTOM: ${modeloPersonalizado}] - ${descripcionProblema.value || 'Sin detalles adicionales'}`
    : (descripcionProblema.value || 'Sin detalles adicionales')

  const idAEnviar = esCustom ? ID_DISPOSITIVO_GENERICO : parametroId

  try {
    const respuesta = await fetch('https://proyectojosetech.onrender.com/api/pedidos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        dispositivo_id: idAEnviar,
        tipo_reparacion: resumenNombresSeleccionados.value,
        descripcion: descripcionFinal,
        precio_estimado: `${sumaTotalEstimada.value}€ Aprox.`
      })
    })

    const datos = await respuesta.json()

    if (respuesta.ok) {
      mensajeExito.value = true
      descripcionProblema.value = ''
      serviciosSeleccionados.value = []
      codigoSeguimientoGenerado.value = datos.pedido.codigo_seguimiento
    } else {
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
  <main class="presupuesto">

    <div v-if="cargando" class="presupuesto__estado" role="alert" aria-busy="true">
      <div class="presupuesto__spinner" aria-hidden="true"></div>
      <p>Sincronizando con bases de datos técnicas de Josetech...</p>
    </div>

    <div v-else-if="error" class="presupuesto__estado presupuesto__estado--error" role="alert">
      <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
      <p>No se pudo obtener la información del dispositivo.</p>
      <router-link to="/solicitar-reparacion" class="presupuesto__btn-volver">Volver al buscador</router-link>
    </div>

    <article v-else class="presupuesto__contenido">

      <div v-if="esCustom" class="presupuesto__alerta-info" role="status">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        <span>Estás solicitando presupuesto para un modelo que no está en nuestro catálogo habitual. Nuestros técnicos
          confirmarán las piezas una vez reciban el equipo.</span>
      </div>

      <header class="presupuesto__cabecera">
        <div class="presupuesto__cabecera-imagen">
          <img v-if="dispositivo.imagen_url" :src="dispositivo.imagen_url" :alt="dispositivo.modelo"
            class="presupuesto__img">
          <i v-else class="fa-solid fa-mobile-screen-button presupuesto__img-placeholder" aria-hidden="true"></i>
        </div>
        <div class="presupuesto__cabecera-info">
          <span class="presupuesto__etiqueta-categoria">{{ dispositivo.tipo }}</span>
          <h1 class="presupuesto__titulo-dispositivo">{{ dispositivo.marca }} <span
              class="presupuesto__titulo-destacado">{{ dispositivo.modelo }}</span></h1>
          <p class="presupuesto__descripcion">Selecciona una o varias reparaciones para combinar tu presupuesto.</p>
        </div>
      </header>

      <section class="presupuesto__servicios" aria-labelledby="titulo-servicios">
        <div class="presupuesto__servicios-header">
          <h2 id="titulo-servicios" class="presupuesto__subtitulo">
            Servicios Disponibles <span class="presupuesto__etiqueta-mo">Mano de obra incluida</span>
          </h2>
          <span class="presupuesto__instruccion">Puedes marcar varios a la vez</span>
        </div>

        <div class="presupuesto__grid-servicios" role="group" aria-label="Lista de reparaciones">
          <button v-for="servicio in serviciosFiltrados" :key="servicio.id" type="button"
            class="presupuesto__tarjeta-servicio"
            :class="{ 'presupuesto__tarjeta-servicio--activa': estaSeleccionado(servicio.id) }"
            :aria-pressed="estaSeleccionado(servicio.id).toString()" @click="toggleServicio(servicio.id)">
            <div class="presupuesto__tarjeta-indicador" aria-hidden="true">
              <i v-if="estaSeleccionado(servicio.id)" class="fa-solid fa-circle-check"></i>
              <span v-else class="presupuesto__circulo-vacio"></span>
            </div>

            <div class="presupuesto__tarjeta-icono" aria-hidden="true">
              <i :class="servicio.icono"></i>
            </div>

            <div class="presupuesto__tarjeta-detalles">
              <h3 class="presupuesto__tarjeta-nombre">{{ servicio.nombre }}</h3>
              <p v-if="servicio.extra_nota" class="presupuesto__tarjeta-nota">{{ servicio.extra_nota }}</p>
            </div>

            <div class="presupuesto__tarjeta-precio">
              {{ servicio.precio }}€ <span class="presupuesto__tarjeta-aprox">Aprox.</span>
            </div>
          </button>
        </div>

        <div class="presupuesto__resumen" v-if="serviciosSeleccionados.length > 0" aria-live="polite">
          <div class="presupuesto__resumen-info">
            <span class="presupuesto__resumen-etiqueta">Reparaciones marcadas ({{ serviciosSeleccionados.length
              }}):</span>
            <span class="presupuesto__resumen-lista">{{ resumenNombresSeleccionados }}</span>
          </div>
          <div class="presupuesto__resumen-total">
            <span class="presupuesto__resumen-etiqueta">Total Estimado:</span>
            <strong class="presupuesto__resumen-cifra">{{ sumaTotalEstimada }}€</strong>
          </div>
        </div>
      </section>

      <div class="presupuesto__info-tecnica">
        <section v-if="ifixitInfo" class="presupuesto__tarjeta-info">
          <header class="presupuesto__tarjeta-head presupuesto__tarjeta-head--naranja">
            <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
            <h3>Análisis de Dificultad iFixit</h3>
          </header>
          <div class="presupuesto__tarjeta-body">
            <div class="presupuesto__fila-datos">
              <span>Dificultad original:</span>
              <span class="presupuesto__pildora"
                :class="'presupuesto__pildora--' + ifixitInfo.dificultad_original.toLowerCase().replace(' ', '-')">
                {{ ifixitInfo.dificultad_original }}
              </span>
            </div>
            <p v-if="ifixitInfo.coste_extra_mano_obra > 0" class="presupuesto__nota-tecnica">
              * Este modelo presenta sellado adhesivo complejo de destapar.<br><br>
              * Este modelo prensenta problemas de acceso a componentes internos.
            </p>
          </div>
        </section>

        <section v-if="icecatInfo" class="presupuesto__tarjeta-info">
          <header class="presupuesto__tarjeta-head presupuesto__tarjeta-head--azul">
            <i class="fa-solid fa-microchip" aria-hidden="true"></i>
            <h3>Disponibilidad Icecat</h3>
          </header>
          <div class="presupuesto__tarjeta-body">
            <div class="presupuesto__fila-datos">
              <span>Fecha de lanzamiento:</span>
              <strong>{{ formatearFecha(icecatInfo.lanzamiento) }}</strong>
            </div>
            <div v-if="icecatInfo.aviso_obsolescencia" class="presupuesto__alerta-peligro" role="alert">
              <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
              <span>Hardware con baja disponibilidad en almacenes.</span>
            </div>
          </div>
        </section>
      </div>

      <section class="presupuesto__contacto" aria-labelledby="titulo-contacto">
        <header class="presupuesto__contacto-header">
          <h3 id="titulo-contacto">Solicitar Recogida Gratuita en Domicilio</h3>
          <p>Un mensajero recogerá tu equipo en el acto. Al procesar el alta confirmas las reparaciones estimadas.</p>
        </header>

        <div v-if="mensajeExito" class="presupuesto__alerta-exito" role="alert" aria-live="assertive">
          <i class="fa-solid fa-circle-check presupuesto__icono-exito" aria-hidden="true"></i>
          <div class="presupuesto__exito-contenido">
            <h4>¡Solicitud enviada con éxito!</h4>
            <p>Hemos registrado tu orden combinada en nuestro taller. Te contactaremos por teléfono para coordinar el
              transporte.</p>

            <div class="presupuesto__caja-tracking">
              <span class="presupuesto__tracking-etiqueta">Tu código de seguimiento es:</span>
              <strong class="presupuesto__tracking-codigo" aria-label="Código de seguimiento">{{
                codigoSeguimientoGenerado }}</strong>
              <span class="presupuesto__tracking-pista">
                <i class="fa-solid fa-user" aria-hidden="true"></i> Puedes consultarlo en tu Perfil de Usuario.
              </span>
            </div>
          </div>
        </div>

        <div v-if="errorEnvio" class="presupuesto__alerta-error" role="alert" aria-live="assertive">
          <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> {{ errorEnvio }}
        </div>

        <form @submit.prevent="enviarSolicitud" v-if="!mensajeExito" class="presupuesto__formulario">
          <label for="descProblema" class="presupuesto__label">¿Presenta algún otro fallo o síntoma el equipo? Cuéntanos
            qué le sucedió al equipo</label>
          <textarea id="descProblema" v-model="descripcionProblema" class="presupuesto__textarea"
            placeholder="Añade observaciones adicionales sobre el estado de la carcasa, si ha sufrido daños por agua, bloqueos, etc."></textarea>

          <button type="submit" class="presupuesto__btn-enviar"
            :disabled="enviando || serviciosSeleccionados.length === 0">
            <span v-if="enviando" class="presupuesto__spinner-boton" aria-hidden="true"></span>
            <span v-else>Confirmar Orden de Reparación ({{ sumaTotalEstimada }}€) <i class="fa-solid fa-truck-fast"
                aria-hidden="true"></i></span>
          </button>
        </form>

        <div v-if="mensajeExito" class="presupuesto__acciones-finales">
          <router-link to="/perfil" class="presupuesto__btn-secundario"><i class="fa-solid fa-user"
              aria-hidden="true"></i> Ir a Mi Perfil</router-link>
        </div>
      </section>

    </article>
  </main>
</template>

<style scoped lang="scss">
/* ==========================================================================
   METODOLOGÍA BEM (Block, Element, Modifier) 
   ========================================================================== */

.presupuesto {
  max-width: 1100px;
  margin: 0 auto;
  padding: 40px 20px;
  min-height: 80vh;
  font-family: 'Inter', system-ui, sans-serif;
  color: var(--text-color);
  animation: fadeIn 0.4s ease-in;

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

  /* --- Estados (Carga / Error) --- */
  &__estado {
    text-align: center;
    padding: 100px 0;
    color: #64748b;

    &--error {
      color: #991b1b;

      i {
        font-size: 3rem;
        margin-bottom: 15px;
      }
    }
  }

  &__spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 4px solid #f1f5f9;
    border-top-color: var(--main-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }
  }

  /* --- Alertas Globales --- */
  &__alerta-info {
    background-color: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e3a8a;
    padding: 15px 20px;
    border-radius: 12px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 25px;

    i {
      font-size: 1.2rem;
      margin-top: 2px;
      color: #3b82f6;
    }
  }

  /* --- Cabecera Dispositivo --- */
  &__cabecera {
    display: flex;
    align-items: center;
    gap: 30px;
    background: white;
    padding: 30px;
    border-radius: 20px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
    margin-bottom: 40px;

    @media (max-width: 768px) {
      flex-direction: column;
      text-align: center;
      padding: 20px;
    }
  }

  &__cabecera-imagen {
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

  &__img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }

  &__img-placeholder {
    font-size: 4rem;
    color: #cbd5e1;
  }

  &__titulo-dispositivo {
    font-size: 2.2rem;
    margin: 8px 0;
    font-weight: 800;
  }

  &__titulo-destacado {
    color: var(--main-color);
  }

  &__descripcion {
    color: #64748b;
    margin: 0;
    font-size: 1rem;
  }

  &__etiqueta-categoria {
    background: #f1f5f9;
    color: #475569;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
  }

  /* --- Servicios y Botones --- */
  &__servicios {
    margin-bottom: 40px;
  }

  &__servicios-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
  }

  &__subtitulo {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 15px;
  }

  &__etiqueta-mo {
    background-color: rgba(var(--main-color-rgb, 217, 106, 26), 0.1);
    color: var(--main-color);
    font-size: 0.8rem;
    padding: 4px 12px;
    border-radius: 12px;
    font-weight: 700;
    text-transform: uppercase;
  }

  &__instruccion {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 600;
  }

  &__grid-servicios {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
    margin-bottom: 25px;
  }

  /* ELEMENTO CLAVE: Botón en lugar de Div */
  &__tarjeta-servicio {
    /* Reset visual de botón */
    appearance: none;
    background: white;
    text-align: left;
    font-family: inherit;
    width: 100%;
    /* Estilos propios */
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;

    &:hover {
      border-color: #cbd5e1;
      transform: translateY(-2px);
    }

    &:focus-visible {
      outline: 3px solid var(--main-color);
      outline-offset: 3px;
    }

    &--activa {
      border-color: var(--main-color);
      background-color: rgba(var(--main-color-rgb, 217, 106, 26), 0.03);
      box-shadow: 0 10px 20px rgba(var(--main-color-rgb, 217, 106, 26), 0.08);

      .presupuesto__tarjeta-icono {
        background-color: var(--main-color);
        color: white;
      }

      .presupuesto__tarjeta-precio {
        color: var(--main-color);
      }
    }
  }

  &__tarjeta-indicador {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 1.3rem;
    color: var(--main-color);
  }

  &__circulo-vacio {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #cbd5e1;
    border-radius: 50%;
  }

  &__tarjeta-icono {
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

  &__tarjeta-detalles {
    margin-bottom: 12px;
  }

  &__tarjeta-nombre {
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0 0 4px 0;
    color: var(--text-color);
  }

  &__tarjeta-nota {
    font-size: 0.75rem;
    color: #94a3b8;
    margin: 0;
    font-weight: 600;
  }

  &__tarjeta-precio {
    font-size: 1.3rem;
    font-weight: 800;
    color: #0f172a;
    border-top: 1px solid #f1f5f9;
    padding-top: 10px;
    transition: color 0.2s;
  }

  &__tarjeta-aprox {
    font-size: 0.8rem;
    font-weight: 600;
    color: #94a3b8;
  }

  /* --- Resumen Inferior --- */
  &__resumen {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: white;
    padding: 20px 25px;
    border-radius: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);

    @media (max-width: 768px) {
      flex-direction: column;
      text-align: center;
    }
  }

  &__resumen-info {
    flex-grow: 1;
    max-width: 70%;

    @media (max-width: 768px) {
      max-width: 100%;
    }
  }

  &__resumen-etiqueta {
    color: #94a3b8;
    font-size: 0.85rem;
    display: block;
    margin-bottom: 3px;
  }

  &__resumen-lista {
    font-weight: 600;
    font-size: 1rem;
    color: white;
    line-height: 1.3;
    display: block;
  }

  &__resumen-total {
    text-align: right;

    @media (max-width: 768px) {
      text-align: center;
    }
  }

  &__resumen-cifra {
    font-size: 1.8rem;
    color: var(--main-color);
    font-weight: 900;
  }

  /* --- Tarjetas de Información --- */
  &__info-tecnica {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
  }

  &__tarjeta-info {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    border: 1px solid #f1f5f9;
  }

  &__tarjeta-head {
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

    &--naranja {
      background: #f97316;
    }

    &--azul {
      background: #3b82f6;
    }
  }

  &__tarjeta-body {
    padding: 20px;
  }

  &__fila-datos {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.95rem;
  }

  &__pildora {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;

    &--easy {
      background: #dcfce7;
      color: #166534;
    }

    &--moderate {
      background: #fef9c3;
      color: #854d0e;
    }

    &--difficult {
      background: #fee2e2;
      color: #991b1b;
    }
  }

  &__nota-tecnica {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 12px;
  }

  &__alerta-peligro {
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

  /* --- Sección Contacto --- */
  &__contacto {
    background: white;
    padding: 35px;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
  }

  &__contacto-header {
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

  &__alerta-exito {
    background-color: #f0fdf4;
    border: 1px solid #bbf7d0;
    padding: 25px;
    border-radius: 16px;
    display: flex;
    gap: 20px;
    align-items: flex-start;
    color: #166534;
    margin-bottom: 20px;

    @media (max-width: 768px) {
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
  }

  &__icono-exito {
    font-size: 2.5rem;
    color: #22c55e;
    margin-top: 5px;
  }

  &__exito-contenido {
    flex-grow: 1;

    h4 {
      margin: 0 0 8px 0;
      font-size: 1.2rem;
      font-weight: 800;
    }

    p {
      margin: 0 0 15px 0;
      font-size: 1rem;
      color: #15803d;
      line-height: 1.5;
    }
  }

  &__caja-tracking {
    background: white;
    border: 2px dashed #86efac;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
  }

  &__tracking-etiqueta {
    display: block;
    font-size: 0.9rem;
    color: #166534;
    font-weight: 600;
    margin-bottom: 5px;
  }

  &__tracking-codigo {
    display: block;
    font-size: 2rem;
    font-weight: 900;
    color: var(--main-color);
    letter-spacing: 2px;
    margin-bottom: 10px;
  }

  &__tracking-pista {
    display: block;
    font-size: 0.85rem;
    color: #15803d;
  }

  &__alerta-error {
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

  &__label {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-color);
    display: block;
    margin-bottom: 10px;
  }

  &__textarea {
    width: 100%;
    height: 110px;
    padding: 15px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 20px;
    font-family: inherit;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.2s;
    resize: vertical;

    &:focus {
      border-color: var(--main-color);
      box-shadow: 0 0 0 3px rgba(217, 106, 26, 0.1);
    }
  }

  &__btn-enviar {
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

    &:focus-visible {
      outline: 3px solid #0f172a;
      outline-offset: 3px;
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }

  &__spinner-boton {
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  &__acciones-finales {
    margin-top: 20px;
    text-align: center;
  }

  &__btn-secundario {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: white;
    background: #0f172a;
    padding: 12px 25px;
    border-radius: 10px;
    font-weight: 700;
    text-decoration: none;
    transition: 0.2s;

    &:hover {
      background: #1e293b;
      transform: translateY(-2px);
    }
  }

  &__btn-volver {
    display: inline-block;
    color: var(--main-color);
    font-weight: 700;
    margin-top: 15px;
    text-decoration: underline;
  }
}
</style>