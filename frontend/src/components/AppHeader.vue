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

        <div class="flex items-center gap-6">
          
          <div v-if="user" class="flex items-center gap-6">
            
            <div class="hidden md:flex items-center gap-6 text-sm font-bold text-gray-600">
                <template v-if="user.role === 'mekletajs'">
                    <router-link to="/offers" class="hover:text-blue-600 transition flex items-center gap-1">
                        <font-awesome-icon icon="search" /> Meklēt Meistarus
                    </router-link>
                    <router-link to="/create-request" class="hover:text-blue-600 transition flex items-center gap-1">
                        <font-awesome-icon icon="plus-circle" /> Ievietot Darbu
                    </router-link>
                    <router-link to="/my-requests" class="hover:text-blue-600 transition flex items-center gap-1">
                        <font-awesome-icon icon="briefcase" /> Mani Darbi
                    </router-link>
                </template>

                <template v-if="user.role === 'meistars'">
                    <router-link to="/requests" class="hover:text-blue-600 transition flex items-center gap-1">
                        <font-awesome-icon icon="search" /> Meklēt Darbus
                    </router-link>
                    <router-link to="/my-offers" class="hover:text-blue-600 transition flex items-center gap-1">
                        <font-awesome-icon icon="tools" /> Mani Pakalpojumi
                    </router-link>
                    <router-link to="/my-applications" class="hover:text-blue-600 transition flex items-center gap-1">
                        <font-awesome-icon icon="list-check" /> Mani Pieteikumi
                    </router-link>
                </template>
            </div>

            <div class="flex items-center gap-3 pl-0 md:pl-6 md:border-l border-gray-200">
                
                <span class="hidden md:flex items-center text-gray-700 font-medium">
                  <font-awesome-icon icon="user" class="text-gray-400 mr-2" />
                  {{ user.username }}
                </span>

                <span @click="$router.push('/dashboard')"
                  class="cursor-pointer text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 px-2 py-1.5 rounded flex items-center font-medium transition"
                  title="Atvērt Paneli">
                  <font-awesome-icon icon="eye" class="text-blue-800 mr-1" />
                  {{ roleNames[user.role] || user.role }}
                </span>

                <button @click="logout" class="text-red-600 hover:text-red-800 font-medium flex items-center gap-2 px-1 ml-1"
                  title="Iziet">
                  <font-awesome-icon icon="sign-out-alt" />
                </button>
            </div>

          </div>

          <div v-else class="flex items-center gap-3">
            <button @click="$router.push('/register')"
              class="hidden sm:block text-blue-600 px-4 py-2 rounded-lg font-bold hover:text-blue-800 hover:bg-gray-50 transition border border-transparent hover:border-blue-100">
              Kļūsti par meistaru
            </button>
            <button @click="$router.push('/login')"
              class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-bold hover:bg-blue-700 shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">
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