import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useRouter } from 'vue-router'

export const useAuthStore = defineStore('auth', () => {
  const usuario = ref(null)
  const router = useRouter()

  // Consulta a Laravel si el token guardado es válido y trae el perfil (con el rol)
  const verificarSesion = async () => {
    const token = localStorage.getItem('auth_token')
    if (!token) {
      usuario.value = null
      return
    }

    try {
      const respuesta = await fetch('https://proyectojosetech.onrender.com/api/user', {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      })

      if (respuesta.ok) {
        usuario.value = await respuesta.json()
      } else {
        cerrarSesion()
      }
    } catch (error) {
      console.error('Error al verificar sesión:', error)
    }
  }

  // Guarda el token y actualiza el usuario al instante
  const iniciarSesion = async (token) => {
    localStorage.setItem('auth_token', token)
    await verificarSesion()
  }

  // Borra el rastro de la sesión
  const cerrarSesion = async () => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      try {
        await fetch('https://proyectojosetech.onrender.com/api/logout', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        })
      } catch (e) {
        console.error(e)
      }
    }

    localStorage.removeItem('auth_token')
    usuario.value = null
  }

  return { usuario, verificarSesion, iniciarSesion, cerrarSesion }
})