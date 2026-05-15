<template>
  <header class="header" :class="{ 'header--oculto': !mostrarNavbar }">
    <router-link to="/" class="header__logo-link">
      <img class="header__logo" src="../../img/logo_sin_fondo.png" alt="Josetech Logo">
    </router-link>

    <button class="header__toggle" @click="toggleMenu" :aria-expanded="menuAbierto.toString()"
      aria-label="Abrir menú de navegación">
      <i :class="menuAbierto ? 'fa-solid fa-xmark' : 'fa-solid fa-bars'"></i>
    </button>

    <nav class="header__nav" :class="{ 'header__nav--abierto': menuAbierto }">
      <ul class="header__nav-list">
        <li><router-link to="/" @click="cerrarMenu">Inicio</router-link></li>
        <li><router-link to="/catalogo" @click="cerrarMenu">Catálogo</router-link></li>
        <li><router-link to="/galeria" @click="cerrarMenu">Galería</router-link></li>
        <li><router-link to="/consulta" @click="cerrarMenu">Consulta</router-link></li>
        <li><router-link to="/quienes-somos" @click="cerrarMenu">Quiénes somos</router-link></li>
        <li v-if="usuarioAutenticado && usuarioAutenticado.rol === 'admin'">
          <router-link to="/admin" @click="cerrarMenu">Panel Admin</router-link>
        </li>
      </ul>

      <div class="header__actions">
        <div v-if="usuarioAutenticado" class="header__user">
          <span class="header__user-name">
            <i class="fa-solid fa-circle-user"></i> {{ usuarioAutenticado.nombre }}
          </span>
          <button @click="cerrarSesion" class="header__btn header__btn--logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Salir
          </button>
        </div>

        <div v-else class="header__auth-buttons">
          <router-link to="/login" class="header__btn" @click="cerrarMenu">
            Iniciar sesión
          </router-link>
          <router-link to="/registro" class="header__btn header__btn--register" @click="cerrarMenu">
            Registrarse
          </router-link>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Variables reactivas
const menuAbierto = ref(false)
const usuarioAutenticado = ref(null)

// Alternar menú en dispositivos móviles
const toggleMenu = () => {
  menuAbierto.value = !menuAbierto.value
}

const cerrarMenu = () => {
  menuAbierto.value = false
}

// === LÓGICA DE LA CABECERA INTELIGENTE (SCROLL) ===
const mostrarNavbar = ref(true)
let ultimoScroll = 0

const manejarScroll = () => {
  // Evitamos que el menú desaparezca si está abierto en el móvil
  if (menuAbierto.value) return;

  const scrollActual = window.scrollY || document.documentElement.scrollTop

  // Si bajamos más de 80px (para no ser muy agresivos al inicio)
  if (scrollActual > ultimoScroll && scrollActual > 80) {
    mostrarNavbar.value = false
  } else {
    // Si subimos, mostramos
    mostrarNavbar.value = true
  }
  ultimoScroll = scrollActual
}

// Comprobar sesión al montar el componente
onMounted(() => {
  verificarSesion()
  window.addEventListener('scroll', manejarScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', manejarScroll)
})

/**
 * Consulta la API de Laravel para ver si el token actual es válido
 * y recuperar los datos del usuario logueado.
 */
const verificarSesion = async () => {
  const token = localStorage.getItem('auth_token')

  if (!token) {
    usuarioAutenticado.value = null
    return
  }

  try {
    const respuesta = await fetch('http://127.0.0.1:8000/api/user', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })

    if (respuesta.ok) {
      usuarioAutenticado.value = await respuesta.json()
    } else {
      localStorage.removeItem('auth_token')
      usuarioAutenticado.value = null
    }
  } catch (error) {
    console.error('Error verificando la sesión:', error)
  }
}

/**
 * Cierra la sesión en el Backend (Sanctum) y limpia el almacenamiento local.
 */
const cerrarSesion = async () => {
  const token = localStorage.getItem('auth_token')

  if (token) {
    try {
      await fetch('http://127.0.0.1:8000/api/logout', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      })
    } catch (error) {
      console.error('Error cerrando sesión en el servidor:', error)
    }
  }

  localStorage.removeItem('auth_token')
  usuarioAutenticado.value = null
  cerrarMenu()
  window.location.href = '/' // Usamos esto para forzar la recarga
}
</script>

<style scoped lang="scss">
.header {
  padding: 15px 60px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: var(--secondary-background-color);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
  
  /* Mantengo position sticky, aunque para transform es mejor fixed, 
     lo dejamos así para no romper tu layout, y le añadimos transition */
  position: sticky;
  top: 0;
  z-index: 50;
  transition: transform 0.4s cubic-bezier(0.3, 1, 0.3, 1), box-shadow 0.3s ease;

  /* Clase que se activa al hacer scroll hacia abajo */
  &--oculto {
    transform: translateY(-100%);
    box-shadow: none;
  }

  &__logo-link {
    display: flex;
    align-items: center;
  }

  &__logo {
    width: 150px;
    height: auto;
    object-fit: contain;
  }

  /* Botón móvil oculto por defecto */
  &__toggle {
    display: none;
    background: transparent;
    border: none;
    font-size: 1.8rem;
    color: var(--main-color);
    cursor: pointer;
    transition: transform 0.3s;

    &:hover {
      transform: scale(1.1);
    }
  }

  &__nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-grow: 1;
    margin-left: 60px;

    &-list {
      display: flex;
      gap: 40px;
      list-style: none;
      margin: 0;
      padding: 0;

      a {
        text-transform: uppercase;
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text-color);
        text-decoration: none;
        transition: color 0.3s, transform 0.3s;
        display: inline-block;

        &:hover,
        &.router-link-active {
          color: var(--main-color);
          transform: translateY(-2px);
        }
      }
    }
  }

  /* Contenedor de botones de usuario */
  &__actions {
    display: flex;
    align-items: center;
  }

  &__auth-buttons {
    display: flex;
    gap: 20px;
  }

  &__user {
    display: flex;
    align-items: center;
    gap: 20px;

    &-name {
      font-weight: 700;
      color: var(--text-color);
      display: flex;
      align-items: center;
      gap: 8px;

      i {
        color: var(--main-color);
        font-size: 1.2rem;
      }
    }
  }

  /* Botones generales */
  &__btn {
    background-color: transparent;
    color: var(--main-color);
    border: 2px solid var(--main-color);
    padding: 10px 24px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;

    &:hover {
      background-color: var(--main-color);
      color: white;
      transform: translateY(-2px);
    }

    &--register {
      background-color: var(--main-color);
      color: white;

      &:hover {
        background-color: var(--secondary-color, #b55612);
        border-color: var(--secondary-color, #b55612);
      }
    }

    &--logout {
      border-color: #ef4444;
      color: #ef4444;

      &:hover {
        background-color: #ef4444;
        color: white;
      }
    }
  }

  /* =========================================
     DISEÑO RESPONSIVO (MÓVILES Y TABLETS)
  ========================================= */
  @media (max-width: 992px) {
    padding: 15px 25px;

    &__nav {
      margin-left: 0;
    }
  }

  @media (max-width: 768px) {
    &__toggle {
      display: block;
    }

    &__nav {
      position: absolute;
      top: 100%;
      left: 0;
      width: 100%;
      background-color: var(--secondary-background-color);
      flex-direction: column;
      align-items: stretch;
      padding: 0;
      max-height: 0;
      overflow: hidden;
      box-shadow: 0 15px 20px rgba(0, 0, 0, 0.1);
      transition: max-height 0.4s cubic-bezier(0.16, 1, 0.3, 1), padding 0.4s ease;

      &--abierto {
        max-height: 500px;
        padding: 25px;
        border-top: 1px solid #eee;
      }

      &-list {
        flex-direction: column;
        gap: 20px;
        text-align: center;
        margin-bottom: 25px;
      }
    }

    &__actions {
      justify-content: center;
      border-top: 1px solid #eee;
      padding-top: 20px;
    }

    &__auth-buttons {
      flex-direction: column;
      width: 100%;

      .header__btn {
        justify-content: center;
        width: 100%;
      }
    }

    &__user {
      flex-direction: column;
      width: 100%;
      gap: 15px;

      &-name {
        justify-content: center;
      }

      .header__btn {
        width: 100%;
        justify-content: center;
      }
    }
  }
}
</style>