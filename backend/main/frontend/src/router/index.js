import { createRouter, createWebHistory } from 'vue-router'
import { getAuthToken } from '@/api/http'
import { refreshSessionUser } from '@/api/authApi'
import { clearLoggedInUserIfBlocked } from '@/utils/authStorage'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import SignupView from '../views/SignupView.vue'
import AdminView from '../views/AdminView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/signup',
      name: 'signup',
      component: SignupView,
    },
    {
      path: '/admin-login',
      redirect: (to) => ({
        path: '/login',
        query: { ...to.query, redirect: '/admin' },
      }),
    },
    {
      path: '/admin',
      name: 'admin',
      component: AdminView,
      meta: { requiresAdmin: true },
    },
    {
      path: '/account',
      name: 'account',
      component: () => import('../views/AccountView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue'),
    },
  ],
})

router.beforeEach(async (to, from, next) => {
  if (to.meta.requiresAdmin) {
    if (!getAuthToken()) {
      next({ path: '/login', query: { redirect: to.fullPath } })
      return
    }
    const blocked = await clearLoggedInUserIfBlocked()
    if (blocked) {
      next({ path: '/login', query: { blocked: '1' } })
      return
    }
    try {
      const user = await refreshSessionUser()
      if (!user.isAdmin) {
        next({ path: '/account', query: { noadmin: '1' } })
        return
      }
    } catch {
      next({ path: '/login', query: { redirect: to.fullPath } })
      return
    }
    next()
    return
  }

  if (to.meta.requiresAuth) {
    if (!getAuthToken()) {
      next('/login')
      return
    }
    const blocked = await clearLoggedInUserIfBlocked()
    if (blocked) {
      next({ path: '/login', query: { blocked: '1' } })
      return
    }
    next()
    return
  }

  next()
})

export default router
