<template>
  <div class="min-h-screen bg-gray-50 font-sans">

    <header class="bg-blue-900 relative overflow-hidden">
      <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 opacity-20">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-blue-500 blur-3xl"></div>
        <div class="absolute top-32 -left-24 w-72 h-72 rounded-full bg-indigo-500 blur-3xl"></div>
      </div>

      <div class="max-w-7xl mx-auto px-4 py-24 sm:px-6 lg:px-8 relative z-10 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-6 leading-tight">
          Atrodi labāko meistaru <br class="hidden md:block" /> savam darbam šodien.
        </h1>
        <p class="text-xl text-blue-100 mb-10 max-w-2xl mx-auto">
          No santehnikas līdz IT risinājumiem. Pievieno savu darbu bez maksas vai atrodi klientus saviem pakalpojumiem.
        </p>

        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <button @click="handlePostJob"
            class="bg-green-500 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-green-600 shadow-lg transition transform hover:scale-105 flex items-center justify-center gap-3">
            <font-awesome-icon icon="pen-to-square" /> Ievietot Darbu
          </button>
          <button @click="$router.push('/requests')"
            class="bg-white text-blue-900 px-8 py-4 rounded-xl font-bold text-lg hover:bg-gray-100 shadow-lg transition transform hover:scale-105 flex items-center justify-center gap-3">
            <font-awesome-icon icon="search" /> Meklēt Darbus
          </button>
        </div>
      </div>
    </header>

    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-12">Kā tas strādā?</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="p-6">
            <div
              class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 text-blue-600">
              <font-awesome-icon icon="bullhorn" />
            </div>
            <h3 class="text-xl font-bold mb-2">1. Ievieto Darbu</h3>
            <p class="text-gray-600">Apraksti, kas jādara, norādi budžetu un termiņu. Tas aizņem tikai 2 minūtes.</p>
          </div>
          <div class="p-6">
            <div
              class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 text-blue-600">
              <font-awesome-icon icon="handshake" />
            </div>
            <h3 class="text-xl font-bold mb-2">2. Saņem Piedāvājumus</h3>
            <p class="text-gray-600">Meistari pieteiksies tavam sludinājumam. Izvēlies labāko pēc atsauksmēm.</p>
          </div>
          <div class="p-6">
            <div
              class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 text-blue-600">
              <font-awesome-icon icon="check-circle" />
            </div>
            <h3 class="text-xl font-bold mb-2">3. Darbs Paveikts</h3>
            <p class="text-gray-600">Sazinies, vienojies un izbaudi rezultātu. Pēc darba atstāj vērtējumu.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-gray-50 border-t">
      <div class="max-w-6xl mx-auto px-4">
        <div v-if="loading" class="text-center py-10 text-gray-400">
          <font-awesome-icon icon="search" spin class="mr-2" /> Ielādē datus...
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="req in latestRequests" :key="req.id"
            class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-6 border border-gray-100 flex flex-col">
            <div class="flex justify-between items-start mb-3">
              <span class="bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 rounded uppercase tracking-wide">{{
                req.category }}</span>
              <span class="text-green-600 font-bold flex items-center gap-1">
                <font-awesome-icon icon="euro-sign" class="text-sm" /> {{ req.budget }}
              </span>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-1">{{ req.title }}</h3>
            <p class="text-gray-500 text-sm mb-4 line-clamp-3 flex-grow">{{ req.description }}</p>
            <div class="flex items-center text-gray-400 text-xs gap-1 mt-auto">
              <font-awesome-icon icon="map-marker-alt" /> <span>{{ req.location }}</span> •
              <span>{{ formatDate(req.created_at) }}</span>
            </div>
          </div>
        </div>

        <div class="mt-8 text-center sm:hidden">
          <button @click="$router.push('/requests')" class="text-blue-600 font-semibold">Skatīt visus darbus →</button>
        </div>

      </div>
    </section>

    <footer class="bg-blue-900 text-blue-200 py-10 text-center">
      <p>&copy; 2026 Meistari.lv. Tavs palīgs darbos.</p>
    </footer>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const user = ref(null);
const requests = ref([]);
const loading = ref(true);

onMounted(async () => {
  const userData = localStorage.getItem('user');
  if (userData) {
    user.value = JSON.parse(userData);
  }

  try {
    const response = await fetch('http://localhost/Meistari/api/get_requests.php');
    if (response.ok) {
      requests.value = await response.json();
    }
  } catch (error) {
    console.error("API Error:", error);
  } finally {
    loading.value = false;
  }
});

const latestRequests = computed(() => {
  return requests.value.slice(0, 3);
});

const handlePostJob = () => {
  if (user.value) {
    router.push('/create-request');
  } else {
    router.push('/login');
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('lv-LV');
};
</script>