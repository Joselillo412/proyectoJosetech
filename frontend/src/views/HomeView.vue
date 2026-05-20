<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

// Variables reactivas del buscador
const busqueda = ref('')
const catalogo = ref([])
const cargando = ref(true)

// Llamada a Laravel para cargar el catálogo global
onMounted(async () => {
  try {
    const respuesta = await fetch('http://127.0.0.1:8000/api/dispositivos')
    const datos = await respuesta.json()
    catalogo.value = datos
  } catch (error) {
    console.error('Error al conectar con el servidor:', error)
  } finally {
    cargando.value = false
  }
})

// Filtro inteligente en tiempo real
const resultadosFiltrados = computed(() => {
  if (busqueda.value.trim() === '') {
    return []
  }
  return catalogo.value.filter(dispositivo => {
    const textoBusqueda = busqueda.value.toLowerCase()
    return dispositivo.modelo.toLowerCase().includes(textoBusqueda) ||
      dispositivo.marca.toLowerCase().includes(textoBusqueda)
  })
})

// === LÓGICA DEL CARRUSEL AUTOMÁTICO ===
const currentIndex = ref(0);
const totalImages = 3;
let intervaloCarrusel = null;

const prevImage = () => {
  currentIndex.value = (currentIndex.value - 1 + totalImages) % totalImages;
  reiniciarTemporizador();
};

const nextImage = () => {
  currentIndex.value = (currentIndex.value + 1) % totalImages;
  reiniciarTemporizador();
};

const iniciarCarrusel = () => {
  intervaloCarrusel = setInterval(() => {
    currentIndex.value = (currentIndex.value + 1) % totalImages;
  }, 5000);
};

const pausarCarrusel = () => {
  if (intervaloCarrusel) {
    clearInterval(intervaloCarrusel);
  }
};

// Reinicia el contador si el usuario pasa la foto a mano para que no le salte la siguiente de golpe
const reiniciarTemporizador = () => {
  pausarCarrusel();
  iniciarCarrusel();
};

// === LÓGICA DE GESTOS TÁCTILES (SWIPE EN MÓVILES) ===
const touchStartX = ref(0);
const touchEndX = ref(0);

const handleTouchStart = (e) => {
  // Registramos la coordenada X donde el usuario pone el dedo
  touchStartX.value = e.changedTouches[0].screenX;
};

const handleTouchEnd = (e) => {
  // Registramos la coordenada X donde levanta el dedo
  touchEndX.value = e.changedTouches[0].screenX;
  procesarDeslizamiento();
};

const procesarDeslizamiento = () => {
  // Distancia mínima en píxeles para considerar que es un swipe intencionado y no un toque casual
  const umbralSwipe = 50;
  const diferencia = touchStartX.value - touchEndX.value;

  if (Math.abs(diferencia) > umbralSwipe) {
    if (diferencia > 0) {
      // Deslizó hacia la izquierda -> Siguiente foto
      nextImage();
    } else {
      // Deslizó hacia la derecha -> Foto anterior
      prevImage();
    }
  }
};

onMounted(() => {
  iniciarCarrusel();
});

onUnmounted(() => {
  pausarCarrusel();
});
</script>

<template>
  <main class="home-container">
    <section class="hero">
      <span class="hero__badge">Laboratorio Electrónico Especializado</span>
      <h1 class="hero__title">¡Josetech Servicio Técnico!</h1>
      <p class="hero__description">
        Gran catálogo de dispositivos para reparar. Busca tu equipo y obtén una estimación de presupuesto al instante.
      </p>

      <i class="hero__arrow fa-solid fa-arrow-down"></i>

      <div class="search-box">
        <div class="search-box__input-container">
          <input type="text" v-model="busqueda"
            placeholder="¿Qué modelo deseas reparar? (Ej: iPhone 13, PS5, MacBook...)" class="search-box__input">
          <button v-if="busqueda" @click="busqueda = ''" class="search-box__clear">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div v-if="resultadosFiltrados.length > 0" class="search-box__grid-container">
          <div class="search-grid">
            <router-link v-for="dispositivo in resultadosFiltrados" :key="dispositivo.id"
              :to="`/presupuesto/${dispositivo.id}`" class="grid-card">
              <div class="grid-card__image-wrapper">
                <img v-if="dispositivo.imagen_url" :src="dispositivo.imagen_url" :alt="dispositivo.modelo"
                  class="grid-card__image">
                <i v-else class="fa-solid fa-mobile-screen-button grid-card__placeholder"></i>
              </div>
              <div class="grid-card__info">
                <span class="grid-card__brand">{{ dispositivo.marca }}</span>
                <span class="grid-card__model">{{ dispositivo.modelo }}</span>
              </div>
            </router-link>
          </div>
        </div>

        <div v-if="busqueda !== '' && resultadosFiltrados.length === 0" class="search-box__empty">
          <p>No hemos encontrado ese dispositivo. ¡Prueba a buscar por marca o modelo!</p>
        </div>
      </div>
    </section>

    <section class="carousel" @mouseenter="pausarCarrusel" @mouseleave="iniciarCarrusel">
      <button class="carousel__left" aria-label="Pasar carrusel para la izquierda" @click="prevImage">
        <i class="fa-solid fa-chevron-left"></i>
      </button>

      <section class="carousel__wrapper" @touchstart="handleTouchStart" @touchend="handleTouchEnd">
        <div class="carousel__galery" :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
          <img class="carousel__galery__image" src="../../img/carousel1.png" alt="Reparar bien es un compromiso">
          <img class="carousel__galery__image" src="../../img/carousel2.png" alt="Herramientas de alta precisión">
          <img class="carousel__galery__image" src="../../img/carousel3.png" alt="Componentes de máxima calidad">
        </div>
      </section>

      <button class="carousel__right" aria-label="Pasar carrusel para la derecha" @click="nextImage">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </section>

    <section class="features-section">
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-card__icon"><i class="feature-card__icon__i fa-solid fa-stopwatch"></i></div>
          <h3>Reparación Rapida</h3>
          <p>Nuestras reparaciones tardan mucho menos tiempo que las de la competencia.</p>
        </div>

        <div class="feature-card">
          <div class="feature-card__icon"><i class="feature-card__icon__i fa-solid fa-calendar-check"></i></div>
          <h3>Sin Cita Previa</h3>
          <p>¿Ganas de esperar? Nosotros tampoco. Haz tu pedido en línea y solo tienes que esperar a que venga el
            repuesto.</p>
        </div>

        <div class="feature-card">
          <div class="feature-card__icon"><i class="feature-card__icon__i fa-solid fa-shield-halved"></i></div>
          <h3>Garantía Certificada</h3>
          <p>Todas nuestras intervenciones y componentes cuentan con garantía profesional, si algo falla, lo arreglamos
            sin coste adicional.</p>
        </div>

        <div class="feature-card">
          <div class="feature-card__icon"><i class="feature-card__icon__i fa-solid fa-microchip"></i></div>
          <h3>Microsoldadura Avanzada</h3>
          <p>No solo cambiamos piezas. Recuperamos placas base, fallos de encendido y daños por líquidos.</p>
        </div>
      </div>
    </section>

    <section class="trust-section">
      <div class="trust-content">
        <h2>¿Pantalla rota? ¿La batería no dura? <br /> ¿Dispositivo lento?</h2>
        <p>Devolvemos la vida a tu smartphone o portátil con repuestos de calidad original.</p>
        <div class="stats-row">
          <div class="stat-item">
            <span class="stat-number">+100</span>
            <span class="stat-label">Equipos Reparados</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">99%</span>
            <span class="stat-label">De acierto <br>(si fallamos pagamos nosotros)</span>
          </div>
          
          <div class="stat-item">
            <span class="stat-number">3 años</span>
            <span class="stat-label">De experiencia</span>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped lang="scss">
.home-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 20px 60px 20px;
  font-family: 'Inter', system-ui, sans-serif;
  color: var(--text-color);
}

.hero {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
  padding: 40px 20px 20px 20px;
  text-align: center;

  &__badge {
    background-color: rgba(var(--main-color-rgb, 217, 106, 26), 0.1);
    color: var(--main-color);
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 15px;
  }

  &__title {
    text-transform: uppercase;
    font-weight: 800;
    font-size: 3rem;
    color: var(--main-color);
    margin: 0 0 15px 0;
    line-height: 1.1;
  }

  &__description {
    font-size: 1.2rem;
    color: #64748b;
    max-width: 650px;
    margin: 0 0 25px 0;
    line-height: 1.5;
  }

  &__arrow {
    font-size: 1.8rem;
    color: var(--main-color);
    animation: bounce 1.5s infinite;
    margin: 10px 0;
  }

  @keyframes bounce {

    0%,
    100% {
      transform: translateY(0);
    }

    50% {
      transform: translateY(10px);
    }
  }
}

.search-box {
  width: 100%;
  max-width: 750px;
  position: relative;
  z-index: 30;

  &__input-container {
    position: relative;
    width: 100%;
  }

  &__input {
    width: 100%;
    padding: 16px 45px 16px 22px;
    border-radius: 16px;
    border: 2px solid var(--main-color);
    font-size: 1.05rem;
    outline: none;
    background-color: white;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);

    &:focus {
      box-shadow: 0 0 12px rgba(var(--main-color-rgb, 217, 106, 26), 0.25);
    }
  }

  &__clear {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: var(--main-color);
    font-size: 1.3rem;
    cursor: pointer;
    padding: 5px;
  }

  &__grid-container {
    position: absolute;
    top: calc(100% + 12px);
    left: 0;
    width: 100%;
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid #e2e8f0;
    max-height: 420px;
    overflow-y: auto;
    padding: 20px;
  }

  &__empty {
    margin-top: 15px;
    color: #ef4444;
    font-weight: 600;
  }
}

.search-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 15px;
}

.grid-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  background-color: #f8fafc;
  border: 1px solid #f1f5f9;
  border-radius: 12px;
  padding: 12px;
  text-decoration: none;
  color: var(--text-color);
  transition: all 0.2s ease;

  &:hover {
    transform: translateY(-3px);
    background-color: white;
    border-color: var(--main-color);
    box-shadow: 0 8px 16px rgba(var(--main-color-rgb, 217, 106, 26), 0.12);

    .grid-card__model {
      color: var(--main-color);
    }
  }

  &__image-wrapper {
    width: 100%;
    height: 85px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
  }

  &__image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }

  &__placeholder {
    font-size: 3rem;
    color: #cbd5e1;
  }

  &__info {
    text-align: center;
    width: 100%;
    overflow: hidden;
  }

  &__brand {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    margin-bottom: 2px;
  }

  &__model {
    display: block;
    font-size: 0.95rem;
    font-weight: 700;
    transition: color 0.2s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}

/* === CARRUSEL === */
.carousel {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  padding: 30px 0;
  width: 100%;
  max-width: 950px;

  &__wrapper {
    overflow: hidden;
    width: 88%;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    background-color: #f8fafc;
    /* Evita el comportamiento de refresco nativo del navegador al hacer swipe horizontal en móviles */
    touch-action: pan-y;
  }

  &__galery {
    display: flex;
    width: 100%;
    transition: transform 0.5s ease-in-out;

    &__image {
      flex: 0 0 100%;
      width: 100%;
      object-fit: contain;
      display: block;
      /* Evita que el usuario seleccione la imagen accidentalmente al intentar deslizar */
      user-select: none;
      -webkit-user-drag: none;
    }
  }

  &__left,
  &__right {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    border-radius: 50%;
    width: 42px;
    height: 42px;
    cursor: pointer;
    z-index: 10;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
      background-color: var(--main-color);
    }
  }

  &__left {
    left: 5px;
  }

  &__right {
    right: 5px;
  }
}

.features-section {
  margin-top: 20px;
  padding: 20px 0;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
}

.feature-card {
  background: white;
  border: 1px solid #f1f5f9;
  border-radius: 16px;
  padding: 30px 20px;
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;

  &__icon {
    &__i {
      font-size: 2.2rem;
    }
  }

  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.08);
    border-color: #e2e8f0;

    .feature-card__icon {
      color: var(--main-color);
      transform: scale(1.1);
    }
  }

  &__icon {
    font-size: 2.2rem;
    color: #94a3b8;
    margin-bottom: 15px;
    transition: all 0.3s ease;
  }

  h3 {
    font-size: 1.2rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    color: var(--text-color);
  }

  p {
    font-size: 0.95rem;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
  }
}

.trust-section {
  margin-top: 50px;
  background: linear-gradient(135deg, var(--secondary-background-color, #f8fafc) 0%, #f1f5f9 100%);
  border-radius: 24px;
  padding: 40px 30px;
  border: 1px solid #e2e8f0;
  text-align: center;
}

.trust-content {
  max-width: 800px;
  margin: 0 auto;

  h2 {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-color);
    margin: 0 0 10px 0;
  }

  p {
    font-size: 1.1rem;
    color: #64748b;
    margin: 0 0 35px 0;
  }
}

.stats-row {
  display: flex;
  justify-content: space-around;
  flex-wrap: wrap;
  gap: 20px;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-number {
  font-size: 2.5rem;
  font-weight: 900;
  color: var(--main-color);
  line-height: 1;
  margin-bottom: 5px;
}

.stat-label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

@media (max-width: 768px) {
  .hero {
    &__title {
      font-size: 2.2rem;
    }

    &__description {
      font-size: 1rem;
    }
  }

  /* === COMPORTAMIENTO MÓVIL DEL CARRUSEL === */
  .carousel {
    &__wrapper {
      width: 100%;
      /* Toma todo el espacio disponible */
      border-radius: 12px;
    }

    &__galery__image {
      max-height: 250px;
    }

    /* ¡PUNTO CLAVE! Ocultamos las flechas nativamente en móviles */
    &__left,
    &__right {
      display: none !important;
    }
  }

  .trust-content h2 {
    font-size: 1.6rem;
  }

  .stat-number {
    font-size: 2rem;
  }
}
</style>