import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import PresupuestoView from '../views/PresupuestoView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/presupuesto/:id',
      name: 'presupuesto',
      component: PresupuestoView
    }
  ]
})

export default router