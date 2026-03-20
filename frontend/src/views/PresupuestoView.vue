<template>
  <div class="max-w-4xl mx-auto p-6 mt-10">
    <div v-if="cargando" class="text-center text-xl">
      Cargando información del equipo... ⏳
    </div>

    <div v-else-if="dispositivo">
      
      <div class="border-b-2 pb-4 mb-6"> <h1 class="text-3xl font-bold">Reparar {{ dispositivo.marca }} {{ dispositivo.modelo }}</h1>
        <p class="text-gray-500 uppercase tracking-wide">{{ dispositivo.tipo }}</p>
      </div>

      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">¿Qué le ocurre a tu dispositivo?</h2>
        
        <div class="space-y-3">
          <label 
            v-for="averia in dispositivo.averias" 
            :key="averia.id"
            class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition"
          >
            <input 
              type="checkbox" 
              :value="averia" 
              v-model="averiasSeleccionadas"
              class="w-5 h-5 text-blue-600 rounded"
            >
            <span class="ml-4 flex-1 text-lg">{{ averia.nombre }}</span>
            <span class="font-bold text-lg">{{ averia.precio }} €</span>
          </label>
        </div>
      </div>

      <div class="bg-gray-100 p-6 rounded-lg text-right">
        <h3 class="text-2xl font-bold mb-2">Total estimado: <span class="text-blue-600">{{ totalEstimado }} €</span></h3>
        
        <button 
          @click="irAlSiguientePaso" 
          :disabled="averiasSeleccionadas.length === 0"
          class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition mt-4"
        >
          Siguiente Paso: Datos de envío ➔
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

// Herramientas de Vue Router
const route = useRoute()
const router = useRouter()

// Variables reactivas (Mi parte de la lógica)
const dispositivo = ref(null)
const cargando = ref(true)
const averiasSeleccionadas = ref([]) // Array que guarda las averías que marca el usuario

// Al cargar la página, pedimos los datos a Laravel usando el ID de la URL
onMounted(async () => {
  try {
    const id = route.params.id
    const respuesta = await fetch(`http://127.0.0.1:8000/api/dispositivos/${id}`)
    dispositivo.value = await respuesta.json()
  } catch (error) {
    console.error('Error al cargar el presupuesto:', error)
  } finally {
    cargando.value = false
  }
})

// Calculadora automática: suma los precios de los checkboxes marcados
const totalEstimado = computed(() => {
  const total = averiasSeleccionadas.value.reduce((suma, averia) => suma + parseFloat(averia.precio), 0)
  return total.toFixed(2) // Formato con 2 decimales
})

// Función para el botón final
const irAlSiguientePaso = () => {
  // Aquí más adelante guardaremos el pedido y enviaremos al cliente al Checkout
  console.log('Averías elegidas:', averiasSeleccionadas.value)
  alert(`¡Has seleccionado reparaciones por ${totalEstimado.value}€! En el siguiente paso haremos la página de Login/Envío.`)
}
</script>