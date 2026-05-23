<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = ref({
  email: '',
  password: ''
})

const errorCredenciales = ref(false)
const mensajeError = ref('')
const cargando = ref(false)

const iniciarSesion = async () => {
  errorCredenciales.value = false
  cargando.value = true

  try {
    const respuesta = await fetch('https://proyectojosetech.onrender.com/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(form.value)
    })

    const datos = await respuesta.json()

    if (respuesta.ok) {
      // Guardamos el token en local
      localStorage.setItem('auth_token', datos.access_token)
      window.location.href = '/';
    } else {
      errorCredenciales.value = true
      mensajeError.value = datos.message || 'Credenciales incorrectas.'
    }
  } catch (error) {
    errorCredenciales.value = true
    mensajeError.value = 'No se pudo conectar con el servidor.'
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <main class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h2>Bienvenido de nuevo a <span>Josetech</span></h2>
        <p>Accede a tu panel de cliente técnico</p>
      </div>

      <div v-if="errorCredenciales" class="alert-error">
        <i class="fa-solid fa-circle-exclamation"></i> {{ mensajeError }}
      </div>

      <form @submit.prevent="iniciarSesion" class="auth-form">
        <div class="input-group">
          <label for="email">Correo electrónico</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-envelope"></i>
            <input id="email" type="email" v-model="form.email" placeholder="ejemplo@correo.com" required>
          </div>
        </div>

        <div class="input-group">
          <label for="password">Contraseña</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-lock"></i>
            <input id="password" type="password" v-model="form.password" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn-submit" :disabled="cargando">
          <span v-if="cargando" class="spinner"></span>
          <span v-else>Entrar</span>
        </button>
      </form>

      <div class="auth-footer">
        <p>¿Aún no tienes cuenta? <router-link to="/registro">Regístrate en un minuto</router-link></p>
      </div>
      <div class="auth-footer">
        <p><router-link to="/forgot-password">¿Olvidaste tu contraseña?</router-link></p>
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
  max-width: 450px;
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;

  .auth-header {
    text-align: center;
    margin-bottom: 30px;

    h2 {
      font-size: 1.8rem;
      color: var(--text-color);
      margin: 0 0 8px 0;

      span {
        color: var(--main-color);
      }
    }

    p {
      color: #64748b;
      margin: 0;
      font-size: 0.95rem;
    }
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
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
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

        &:focus {
          border-color: var(--main-color);
        }
      }
    }
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

  &:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }
}

.spinner {
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

.auth-footer {
  text-align: center;
  margin-top: 25px;
  font-size: 0.9rem;
  color: #64748b;

  a {
    color: var(--main-color);
    font-weight: 700;
    text-decoration: none;
  }
}
</style>