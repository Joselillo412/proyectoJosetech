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
    },
    {
      path: '/catalogo',
      name: 'catalogo',
      component: () => import('../views/CatalogView.vue')
    },
    {
      path: '/catalogo/moviles',
      name: 'moviles',
      component: () => import('../views/MovilesView.vue')
    },
    {
      path: '/catalogo/moviles/apple',
      name: 'Apple',
      component: () => import('../views/AppleView.vue')
    },
    {
      path: '/catalogo/moviles/samsung',
      name: 'Samsung',
      component: () => import('../views/SamsungView.vue')
    },
    {
      path: '/catalogo/moviles/xiaomi',
      name: 'Xiaomi',
      component: () => import('../views/XiaomiView.vue')
    },
    {
      path: '/catalogo/moviles/google',
      name: 'Google',
      component: () => import('../views/GoogleView.vue')
    },
    {
      path: '/catalogo/moviles/onePlus',
      name: 'OnePlus',
      component: () => import('../views/OnePlusView.vue')
    },
    {
      path: '/catalogo/moviles/huawei',
      name: 'Huawei',
      component: () => import('../views/HuaweiView.vue')
    },
    {
      path: '/catalogo/moviles/honor',
      name: 'Honor',
      component: () => import('../views/HonorView.vue')
    },
    {
      path: '/catalogo/moviles/nothing',
      name: 'Nothing',
      component: () => import('../views/NothingView.vue')
    },
    {
      path: '/catalogo/moviles/motorola',
      name: 'Motorola',
      component: () => import('../views/MotorolaView.vue')
    },
    {
      path: '/catalogo/moviles/poco',
      name: 'Poco',
      component: () => import('../views/PocoView.vue')
    },
    {
      path: '/catalogo/moviles/vivo',
      name: 'Vivo',
      component: () => import('../views/VivoView.vue')
    },
    {
      path: '/catalogo/moviles/oppo',
      name: 'Oppo',
      component: () => import('../views/OppoView.vue')
    },
    {
      path: '/catalogo/moviles/realme',
      name: 'Realme',
      component: () => import('../views/RealmeView.vue')
    },
    {
      path: '/galeria',
      name: 'galeria',
      component: () => import('../views/GaleriaView.vue')
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue')
    },
    {
      path: '/registro',
      name: 'registro',
      component: () => import('../views/RegisterView.vue')
    },
    {
      path: '/admin',
      name: 'adminPanel',
      component: () => import('../views/AdminPanelView.vue'),
      meta: { requiresAdmin: true }
    },
    {
      path: '/forgot-password',
      name: 'forgotPassword',
      component: () => import('../views/ForgotPasswordView.vue')
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  if (to.meta.requiresAdmin) {
    const token = localStorage.getItem('auth_token')

    if (!token) {
      return next('/login')
    }

    try {
      const respuesta = await fetch('http://127.0.0.1:8000/api/user', {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })

      if (respuesta.ok) {
        const usuario = await respuesta.json()
        if (usuario.rol === 'admin') {
          return next()
        }
      }
    } catch (error) {
      console.error(error)
    }

    return next('/')
  }

  next()
})

export default router