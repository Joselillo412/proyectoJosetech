<template>
  <div>
    <div>
      <input 
        type="text" 
        v-model="busqueda" 
        placeholder="Ej: iPhone 13, PS5, MacBook..." 
        class="border-2 border-gray-300 p-2" >
    </div>

    <div v-if="cargando">
      <p>Cargando catálogo...</p>
    </div>

    <div v-else-if="resultadosFiltrados.length > 0">
      <ul>
        <li v-for="dispositivo in resultadosFiltrados" :key="dispositivo.id">
          <router-link :to="`/presupuesto/${dispositivo.id}`">
            {{ dispositivo.marca }} {{ dispositivo.modelo }} ({{ dispositivo.tipo }})
          </router-link>
        </li>
      </ul>
    </div>

    <div v-else-if="busqueda !== '' && resultadosFiltrados.length === 0">
      <p>No hemos encontrado ese dispositivo. ¡Prueba con otro!</p>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Variables reactivas (El estado de nuestra aplicación)
const busqueda = ref('') // Lo que el usuario escribe en el input
const catalogo = ref([]) // Aquí guardaremos los +100 dispositivos de Laravel
const cargando = ref(true) // Para mostrar un "Cargando..." mientras responde el servidor

// Cuando la página se carga, llamamos a Laravel
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

// Función inteligente que filtra el catálogo en tiempo real
const resultadosFiltrados = computed(() => {
  // Si el buscador está vacío, no mostramos nada (o puedes cambiarlo para que muestre todos)
  if (busqueda.value.trim() === '') {
    return []
  }

  // Filtramos por marca o por modelo ignorando mayúsculas/minúsculas
  return catalogo.value.filter(dispositivo => {
    const textoBusqueda = busqueda.value.toLowerCase()
    return dispositivo.modelo.toLowerCase().includes(textoBusqueda) || 
           dispositivo.marca.toLowerCase().includes(textoBusqueda)
  })
})

</script>