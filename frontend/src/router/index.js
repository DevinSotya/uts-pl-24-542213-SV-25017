import { createRouter, createWebHistory } from 'vue-router'
import { getToken } from '../api/axios'
import LoginView from '../views/LoginView.vue'
import BooksView from '../views/BooksView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/books' },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/books', name: 'books', component: BooksView, meta: { requiresAuth: true } },
    { path: '/:pathMatch(.*)*', redirect: '/books' },
  ],
})

// Halaman yang butuh token tidak bisa dibuka tanpa login.
router.beforeEach((to) => {
  const loggedIn = !!getToken()
  if (to.meta.requiresAuth && !loggedIn) return { name: 'login' }
  if (to.name === 'login' && loggedIn) return { name: 'books' }
})

export default router