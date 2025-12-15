import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import DashboardView from '../views/DashboardView.vue'
import ProfileView from '../views/ProfileView.vue'
import CreateRequestView from '../views/CreateRequestView.vue'
import RequestsView from '../views/RequestsView.vue'
import HomeView from '../views/HomeView.vue'
import MyRequestsView from '../views/MyRequestsView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView
    },
    { path: '/register', name: 'register', component: RegisterView },
    { 
      path: '/dashboard', 
      name: 'dashboard', 
      component: DashboardView
    },
    { path: '/profile', name: 'profile', component: ProfileView },
    { path: '/create-request', name: 'create-request', component: CreateRequestView },
    { path: '/requests', name: 'requests', component: RequestsView },
    { path: '/my-requests', name: 'my-requests', component: MyRequestsView },
  ]
})

export default router