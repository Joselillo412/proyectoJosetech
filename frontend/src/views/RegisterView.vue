<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Variables del formulario
const form = ref({
  nombre: '',
  email: '',
  telefono: '',
  password: '',
  password_confirmation: ''
})

const errores = ref({})
const cargando = ref(false)

const registrarUsuario = async () => {
  errores.value = {}
  cargando.value = true

  try {
    const respuesta = await fetch('http://127.0.0.1:8000/api/registro', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(form.value)
    })

    const datos = await respuesta.json()

    if (respuesta.ok) {
      // Guardamos el token de Sanctum en el almacenamiento local
      localStorage.setItem('auth_token', datos.access_token)
      
      // Forzamos la redirección a la home (el Navbar detectará el token automáticamente)
      window.location.href = '/';
    } else {
      // Capturamos errores de validación de Laravel (ej. email repetido, contraseñas cortas)
      if (datos.errors) {
        errores.value = datos.errors
      } else {
        errores.value = { general: [datos.message || 'Error al procesar el registro.'] }
      }
    }
  } catch (error) {
    errores.value = { general: ['Error de conexión con el servidor.'] }
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h2>Crear Cuenta en <span>Josetech</span></h2>
        <p>Regístrate para gestionar tus pedidos y presupuestos</p>
      </div>

      <div v-if="errores.general" class="alert-error">
        {{ errores.general[0] }}
      </div>

      <form @submit.prevent="registrarUsuario" class="auth-form">
        <div class="input-group">
          <label for="nombre">Nombre completo</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-user"></i>
            <input 
              id="nombre" 
              type="text" 
              v-model="form.nombre" 
              placeholder="Ej: Juan Pérez" 
              required
            >
          </div>
          <span v-if="errores.nombre" class="error-text">{{ errores.nombre[0] }}</span>
        </div>

        <div class="input-group">
          <label for="email">Correo electrónico</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-envelope"></i>
            <input 
              id="email" 
              type="email" 
              v-model="form.email" 
              placeholder="ejemplo@gmail.com" 
              required
            >
          </div>
          <span v-if="errores.email" class="error-text">{{ errores.email[0] }}</span>
        </div>

        <div class="input-group">
          <label for="telefono">Teléfono de contacto</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-phone"></i>
            <input 
              id="telefono" 
              type="tel" 
              v-model="form.telefono" 
              placeholder="Ej: 600 000 000" 
              required
            >
          </div>
          <span v-if="errores.telefono" class="error-text">{{ errores.telefono[0] }}</span>
        </div>

        <div class="input-group">
          <label for="password">Contraseña</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-lock"></i>
            <input 
              id="password" 
              type="password" 
              v-model="form.password" 
              placeholder="Mínimo 8 caracteres" 
              required
            >
          </div>
          <span v-if="errores.password" class="error-text">{{ errores.password[0] }}</span>
        </div>

        <div class="input-group">
          <label for="password_confirmation">Confirmar Contraseña</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-lock"></i>
            <input 
              id="password_confirmation" 
              type="password" 
              v-model="form.password_confirmation" 
              placeholder="Repite la contraseña" 
              required
            >
          </div>
        </div>

        <button type="submit" class="btn-submit" :disabled="cargando">
          <span v-if="cargando" class="spinner"></span>
          <span v-else>Completar Registro</span>
        </button>
      </form>

      <div class="auth-footer">
        <p>¿Ya tienes una cuenta? <router-link to="/login">Inicia sesión aquí</router-link></p>
      </div>
    </div>
  </main>
</template>

<style scoped lang="scss">
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 100px);
  padding: 40px 20px;
  background-color: var(--secondary-background-color, #f8fafc);
}

.auth-card {
  background: white;
  width: 100%;
  max-width: 500px;
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;

  .auth-header {
    text-align: center;
    margin-bottom: 30px;

    h2 {
      font-size: 2rem;
      color: var(--text-color);
      margin: 0 0 8px 0;
      span { color: var(--main-color); }
    }

    p { color: #64748b; margin: 0; font-size: 0.95rem; }
  }
}

.alert-error {
  background-color: #fef2f2;
  border: 1px solid #fee2e2;
  color: #991b1b;
  padding: 12px;
  border-radius: 10px;
  font-size: 0.9rem;
  margin-bottom: 20px;
  text-align: center;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 20px;

  .input-group {
    display: flex;
    flex-direction: column;
    gap: 6px;

    label {
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--text-color);
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;

      i {
        position: absolute;
        left: 15px;
        color: #94a3b8;
        font-size: 1rem;
      }

      input {
        width: 100%;
        padding: 14px 15px 14px 45px;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        outline: none;
        font-size: 0.95rem;
        transition: border-color 0.3s;

        &:focus { border-color: var(--main-color); }
      }
    }

    .error-text { font-size: 0.8rem; color: #ef4444; }
  }
}

.btn-submit {
  background-color: var(--main-color);
  color: white;
  border: none;
  padding: 16px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 10px;

  &:hover:not(:disabled) {
    background-color: var(--secondary-color, #b55612);
    transform: translateY(-2px);
  }

  &:disabled { opacity: 0.7; cursor: not-allowed; }
}

.spinner {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.auth-footer {
  text-align: center;
  margin-top: 25px;
  font-size: 0.9rem;
  color: #64748b;

  a { color: var(--main-color); font-weight: 700; text-decoration: none; }
}
</style>