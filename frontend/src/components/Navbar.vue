<template>
  <nav class="bg-blue-700 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex-shrink-0 flex items-center">
          <router-link to="/" class="text-2xl font-bold tracking-wider">
            🔧 JOSÉ<span class="text-blue-300">TECH</span>
          </router-link>
        </div>

        <div class="hidden md:flex space-x-8">
          <router-link to="/" class="hover:text-blue-300 transition duration-300">Inicio</router-link>
          <router-link to="/catalogo" class="hover:text-blue-300 transition duration-300">Catálogo</router-link>
          <router-link to="/mis-pedidos" class="hover:text-blue-300 transition duration-300">Mis Pedidos</router-link>
        </div>

        <div class="hidden md:flex items-center">
          <router-link to="/login" class="bg-white text-blue-700 px-4 py-2 rounded-md font-semibold hover:bg-gray-100 transition duration-300">
            Iniciar Sesión
          </router-link>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
// Más adelante aquí meteremos la lógica para saber si el usuario está logueado
</script>