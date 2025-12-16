<template>
  <nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-20 items-center">
        
        <div class="flex items-center gap-2 cursor-pointer" @click="$router.push('/')">
          <font-awesome-icon icon="hammer" class="text-blue-600 text-2xl" />
          <span class="text-3xl font-extrabold text-blue-900 tracking-tight">
            Meistari<span class="text-blue-500">.lv</span>
          </span>
        </div>
        
        <div class="flex items-center gap-4">
          
          <div v-if="user" class="flex items-center gap-4">
            <span class="hidden md:block text-gray-700 font-medium">
              <font-awesome-icon icon="user" class="text-gray-400 mr-2"/>
              {{ user.username }} 
              <span @click="$router.push('/dashboard')" class="cursor-pointer text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 px-2 py-1 rounded ml-1 transition"><font-awesome-icon icon="eye" class="text-blue-80 pr-1" />{{ roleNames[user.role] || user.role }}</span>
            </span>

            <button 
              @click="logout" 
              class="text-red-600 hover:text-red-800 font-medium flex items-center gap-2 px-2"
              title="Iziet"
            >
              <font-awesome-icon icon="sign-out-alt" />
            </button>
          </div>

          <div v-else class="flex items-center gap-3">
            <button 
              @click="$router.push('/register')" 
              class="text-blue-600 px-5 py-2.5 rounded-lg font-bold hover:text-blue-800 hover:bg-gray-100 shadow-lg transition"
            >
              Kļūsti par meistaru
            </button>
            <button 
              @click="$router.push('/login')" 
              class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-bold hover:bg-blue-700 shadow-lg transition"
            >
              Pieslēgties
            </button>
          </div>

        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const user = ref(null);
const router = useRouter();
const route = useRoute();

const roleNames = {
  'mekletajs': 'Meklētājs',
  'meistars': 'Meistars',
  'admin': 'Administrators'
};
const checkUser = () => {
  const userData = localStorage.getItem('user');
  if (userData) {
    user.value = JSON.parse(userData);
  } else {
    user.value = null;
  }
};

// Pārbaudām lietotāju, kad komponente ielādējas
onMounted(() => {
  checkUser();
});

watch(route, () => {
  checkUser();
});

const logout = () => {
  localStorage.removeItem('user');
  user.value = null;
  router.push('/login');
};
</script>