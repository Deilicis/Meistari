import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import DashboardView from '../views/DashboardView.vue'
import ProfileView from '../views/ProfileView.vue'
import CreateRequestView from '../views/CreateRequestView.vue'
import RequestsView from '../views/RequestsView.vue'
import HomeView from '../views/HomeView.vue'
import MyRequestsView from '../views/MyRequestsView.vue'
import AdminView from '../views/AdminView.vue'

// frontend/src/router/index.js

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
      component: LoginView,
      meta: { hideHeader: true }
    },
    { 
      path: '/register', 
      name: 'register', 
      component: RegisterView,
      meta: { hideHeader: true }
    },
    { 
      path: '/dashboard', 
      name: 'dashboard', 
      component: DashboardView 
    },
    { path: '/profile', name: 'profile', component: ProfileView },
    { path: '/create-request', name: 'create-request', component: CreateRequestView },
    { path: '/requests', name: 'requests', component: RequestsView },
    { path: '/my-requests', name: 'my-requests', component: MyRequestsView },
    { 
      path: '/admin', 
      name: 'admin', 
      component: AdminView,
      meta: { hideHeader: true }
    },
  ]
})

// Globālais Navigācijasargs (Navigation Guard)
router.beforeEach((to, from, next) => {
  const userData = localStorage.getItem('user');
  
  if (userData) {
    const user = JSON.parse(userData);
    const now = new Date().getTime();

    // Pārbaudām, vai sesijas laiks ir beidzies vai nav iestatīts
    if (!user.expiry || now > user.expiry) {
      // Sesija beigusies - dzēšam datus un metam ārā
      localStorage.removeItem('user');
      
      // Ja lietotājs jau nav login lapā, pārvirzām
      if (to.name !== 'login' && to.name !== 'register') {
        return next({ name: 'login' });
      }
    }
  }
  
  // Ja lietotājs mēģina piekļūt slēgtām lapām bez ielogošanās
  const publicPages = ['login', 'register', 'home'];
  const authRequired = !publicPages.includes(to.name);
  
  if (authRequired && !userData) {
    return next({ name: 'login' });
  }

  next();
});

export default router