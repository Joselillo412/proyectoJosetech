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
      component: () => import('../views/AppleIphoneView.vue')
    },
    {
      path: '/catalogo/moviles/samsung',
      name: 'Samsung',
      component: () => import('../views/SamsungMovilView.vue')
    },
    {
      path: '/catalogo/moviles/xiaomi',
      name: 'Xiaomi',
      component: () => import('../views/XiaomiMovilView.vue')
    },
    {
      path: '/catalogo/moviles/google',
      name: 'Google',
      component: () => import('../views/GoogleMovilView.vue')
    },
    {
      path: '/catalogo/moviles/onePlus',
      name: 'OnePlus',
      component: () => import('../views/OnePlusMovilView.vue')
    },
    {
      path: '/catalogo/moviles/huawei',
      name: 'Huawei',
      component: () => import('../views/HuaweiMovilView.vue')
    },
    {
      path: '/catalogo/moviles/honor',
      name: 'Honor',
      component: () => import('../views/HonorMovilView.vue')
    },
    {
      path: '/catalogo/moviles/nothing',
      name: 'Nothing',
      component: () => import('../views/NothingView.vue')
    },
    {
      path: '/catalogo/moviles/motorola',
      name: 'Motorola',
      component: () => import('../views/MotorolaMovilView.vue')
    },
    {
      path: '/catalogo/moviles/poco',
      name: 'Poco',
      component: () => import('../views/PocoMovilView.vue')
    },
    {
      path: '/catalogo/moviles/vivo',
      name: 'Vivo',
      component: () => import('../views/VivoMovilView.vue')
    },
    {
      path: '/catalogo/moviles/oppo',
      name: 'Oppo',
      component: () => import('../views/OppoMovilView.vue')
    },
    {
      path: '/catalogo/moviles/realme',
      name: 'Realme',
      component: () => import('../views/RealmeMovilView.vue')
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
    },
    {
      path: '/consulta',
      name: 'consulta',
      component: () => import('../views/ConsultaView.vue')
    },
    {
      path: '/perfil',
      name: 'perfil',
      component: () => import('../views/ProfileView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/solicitar-reparacion',
      name: 'solicitarReparacion',
      component: () => import('../views/SolicitarReparacionView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/catalogo/tablets',
      name: 'tablets',
      component: () => import('../views/TabletsView.vue')
    },
    {
      path: '/catalogo/tablets/apple',
      name: 'AppleTablets',
      component: () => import('../views/AppleAipadView.vue')
    },
    {
      path: '/catalogo/tablets/samsung',
      name: 'SamsungTablets',
      component: () => import('../views/SamsungTabletView.vue')
    },
    {
      path: '/catalogo/tablets/xiaomi',
      name: 'XiaomiTablets',
      component: () => import('../views/XiaomiTabletView.vue')
    },
    {
      path: '/catalogo/tablets/google',
      name: 'GoogleTablets',
      component: () => import('../views/GoogleTabletView.vue')
    },
    {
      path: '/catalogo/tablets/onePlus',
      name: 'OnePlusTablets',
      component: () => import('../views/OnePlusTabletView.vue')
    },
    {
      path: '/catalogo/tablets/huawei',
      name: 'HuaweiTablets',
      component: () => import('../views/HuaweiTabletView.vue')
    },
    {
      path: '/catalogo/tablets/honor',
      name: 'HonorTablets',
      component: () => import('../views/HonorTabletView.vue')
    },
    {
      path: '/catalogo/tablets/lenovo',
      name: 'LenovoTablets',
      component: () => import('../views/LenovoView.vue')
    },
    {
      path: '/catalogo/tablets/motorola',
      name: 'MotorolaTablets',
      component: () => import('../views/MotorolaTabletView.vue')
    },
    {
      path: '/catalogo/tablets/poco',
      name: 'PocoTablets',
      component: () => import('../views/PocoTabletView.vue')
    },
    {
      path: '/catalogo/tablets/vivo',
      name: 'VivoTablets',
      component: () => import('../views/VivoTabletView.vue')
    },
    {
      path: '/catalogo/tablets/oppo',
      name: 'OppoTablets',
      component: () => import('../views/OppoTabletView.vue')
    },
    {
      path: '/catalogo/tablets/realme',
      name: 'RealmeTablets',
      component: () => import('../views/RealmeTabletView.vue')
    },
    {
      path: '/catalogo/consolas',
      name: 'consolas',
      component: () => import('../views/ConsolasView.vue')
    },
    {
      path: '/catalogo/consolas/sony',
      name: 'SonyConsolas',
      component: () => import('../views/SonyConsolaView.vue')
    },
    {
      path: '/catalogo/consolas/microsoft',
      name: 'MicrosoftConsolas',
      component: () => import('../views/MicrosoftConsolaView.vue')
    },
    {
      path: '/catalogo/consolas/nintendo',
      name: 'NintendoConsolas',
      component: () => import('../views/NintendoConsolaView.vue')
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