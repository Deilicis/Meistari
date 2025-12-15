<template>
  <div class="min-h-screen bg-gray-50 font-sans">
    
   <header 
      class="relative overflow-hidden bg-cover bg-center bg-no-repeat"
      style="background-image: url('assets/hero-bg.png');"
    >
      
      <div class="absolute inset-0 bg-blue-900/100 z-0"></div>

      <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 opacity-30">
         <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-blue-500 blur-3xl"></div>
         <div class="absolute top-32 -left-24 w-72 h-72 rounded-full bg-indigo-500 blur-3xl"></div>
      </div>

      <div class="max-w-7xl mx-auto px-4 py-24 sm:px-6 lg:px-8 relative z-10 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-6 leading-tight drop-shadow-md">
          Atrodi labāko meistaru <br class="hidden md:block" /> savam darbam šodien.
        </h1>
        <p class="text-xl text-blue-100 mb-10 max-w-2xl mx-auto drop-shadow-sm">
          No santehnikas līdz IT risinājumiem. Pievieno savu darbu bez maksas vai atrodi klientus saviem pakalpojumiem.
        </p>
        
        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <button @click="handlePostJob" class="bg-green-500 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-green-600 shadow-lg transition transform hover:scale-105 flex items-center justify-center gap-3">
            <font-awesome-icon icon="pen-to-square" /> Ievietot Darbu
          </button>
          <button @click="$router.push('/requests')" class="bg-white text-blue-900 px-8 py-4 rounded-xl font-bold text-lg hover:bg-gray-100 shadow-lg transition transform hover:scale-105 flex items-center justify-center gap-3">
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
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 text-blue-600">
               <font-awesome-icon icon="bullhorn" />
            </div>
            <h3 class="text-xl font-bold mb-2">1. Ievieto Darbu</h3>
            <p class="text-gray-600">Apraksti, kas jādara, norādi budžetu un termiņu.</p>
          </div>
          <div class="p-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 text-blue-600">
                <font-awesome-icon icon="handshake" />
            </div>
            <h3 class="text-xl font-bold mb-2">2. Saņem Piedāvājumus</h3>
            <p class="text-gray-600">Meistari pieteiksies tavam sludinājumam.</p>
          </div>
          <div class="p-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 text-blue-600">
                <font-awesome-icon icon="check-circle" />
            </div>
            <h3 class="text-xl font-bold mb-2">3. Darbs Paveikts</h3>
            <p class="text-gray-600">Sazinies, vienojies un izbaudi rezultātu.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-gray-50 border-t">
      <div class="max-w-6xl mx-auto px-4">
        
        <div class="flex justify-center mb-10">
            <div class="bg-white p-1 rounded-full shadow-sm border inline-flex">
                <button 
                    @click="activeTab = 'requests'"
                    class="px-8 py-3 rounded-full text-sm font-bold transition-all duration-300 flex items-center gap-2"
                    :class="activeTab === 'requests' ? 'bg-blue-900 text-white shadow-md' : 'text-gray-500 hover:text-blue-900'"
                >
                    <font-awesome-icon icon="briefcase" /> Meklētāju Pieprasījumi
                </button>
                <button 
                    @click="activeTab = 'offers'"
                    class="px-8 py-3 rounded-full text-sm font-bold transition-all duration-300 flex items-center gap-2"
                    :class="activeTab === 'offers' ? 'bg-blue-900 text-white shadow-md' : 'text-gray-500 hover:text-blue-900'"
                >
                    <font-awesome-icon icon="hammer" /> Meistaru Pakalpojumi
                </button>
            </div>
        </div>

        <div class="flex justify-between items-end mb-8">
          <div>
            <h2 class="text-3xl font-bold text-gray-900">
                {{ activeTab === 'requests' ? 'Jaunākie Darba Sludinājumi' : 'Jaunākie Meistaru Piedāvājumi' }}
            </h2>
            <p class="text-gray-600 mt-2">
                {{ activeTab === 'requests' ? 'Aktīvi darbi, kuriem nepieciešami meistari.' : 'Profesionāļi, kas gatavi palīdzēt.' }}
            </p>
          </div>
          <button @click="$router.push('/requests')" class="text-blue-600 font-semibold hover:underline hidden sm:block">
            Skatīt visus →
          </button>
        </div>

        <div v-if="loading" class="text-center py-10 text-gray-400">
            <font-awesome-icon icon="search" spin class="mr-2"/> Ielādē datus...
        </div>

        <div v-else-if="activeTab === 'requests'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="req in latestRequests" :key="req.id" class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-6 border border-gray-100 flex flex-col">
            <div class="flex justify-between items-start mb-3">
              <span class="bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 rounded uppercase tracking-wide">{{ req.category }}</span>
              <span class="text-green-600 font-bold flex items-center gap-1">
                <font-awesome-icon icon="euro-sign" class="text-sm"/> {{ req.budget }}
              </span>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-1">{{ req.title }}</h3>
            <p class="text-gray-500 text-sm mb-4 line-clamp-3 flex-grow">{{ req.description }}</p>
            <div class="flex items-center text-gray-400 text-xs gap-1 mt-auto pt-4 border-t">
              <font-awesome-icon icon="map-marker-alt" /> <span>{{ req.location }}</span> • 
              <span>{{ formatDate(req.created_at) }}</span>
            </div>
          </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="offer in latestOffers" :key="offer.id" class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-6 border border-gray-100 flex flex-col group">
            <div class="flex justify-between items-start mb-3">
              <span class="bg-purple-50 text-purple-700 text-xs font-bold px-2 py-1 rounded uppercase tracking-wide">{{ offer.category }}</span>
              <span class="text-green-600 font-bold flex items-center gap-1">
                <font-awesome-icon icon="euro-sign" class="text-sm"/> {{ offer.price }}
              </span>
            </div>
            
            <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-1 group-hover:text-blue-600 transition">{{ offer.title }}</h3>
            <p class="text-gray-500 text-sm mb-4 line-clamp-3 flex-grow">{{ offer.description }}</p>
            
            <div class="mt-auto pt-4 border-t flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs">
                        <font-awesome-icon icon="user" />
                    </div>
                    <div class="text-xs">
                        <p class="font-bold text-gray-700">{{ getMasterName(offer) }}</p>
                        <p class="text-gray-400" v-if="offer.city"><font-awesome-icon icon="map-marker-alt" /> {{ offer.city }}</p>
                    </div>
                </div>
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
const offers = ref([]); 
const loading = ref(true);
const activeTab = ref('requests'); 

onMounted(async () => {
  const userData = localStorage.getItem('user');
  if (userData) user.value = JSON.parse(userData);

  try {
    const [reqResponse, offResponse] = await Promise.all([
        fetch('http://localhost/Meistari/api/get_requests.php'),
        fetch('http://localhost/Meistari/api/get_offers.php')
    ]);

    if (reqResponse.ok) requests.value = await reqResponse.json();
    if (offResponse.ok) offers.value = await offResponse.json();

  } catch (error) {
    console.error("API Error:", error);
  } finally {
    loading.value = false;
  }
});

// tikai pirmos 3 ierakstus
const latestRequests = computed(() => requests.value.slice(0, 3));
const latestOffers = computed(() => offers.value.slice(0, 3));

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


const getMasterName = (offer) => {
    if (offer.company_name) return offer.company_name;
    if (offer.first_name) return `${offer.first_name} ${offer.last_name}`;
    return offer.username; 
};
</script>