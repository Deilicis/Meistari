<template>
  <div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-6xl mx-auto px-4">
      
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
           <h2 class="text-3xl font-bold text-gray-800">Aktīvie Darba Sludinājumi</h2>
           <p class="text-gray-500">Atrodi sev piemērotāko darbu un piesakies.</p>
        </div>
        <button v-if="user && user.role === 'mekletajs'" @click="$router.push('/create-request')" class="bg-green-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-green-700 transition shadow-md">
            <font-awesome-icon icon="plus-circle" /> Ievietot Jaunu
        </button>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <div class="md:col-span-2 relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <font-awesome-icon icon="search" />
                </span>
                <input 
                    v-model="filters.search" 
                    type="text" 
                    placeholder="Meklēt pēc nosaukuma..." 
                    class="w-full pl-10 border p-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50 focus:bg-white transition"
                >
            </div>

            <div>
                <select v-model="filters.category" class="w-full border p-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50 focus:bg-white transition text-gray-700">
                    <option value="">Visas kategorijas</option>
                    <option value="Santehnika">Santehnika</option>
                    <option value="Elektrība">Elektrība</option>
                    <option value="Celtniecība">Celtniecība</option>
                    <option value="IT Pakalpojumi">IT Pakalpojumi</option>
                    <option value="Tīrīšana">Tīrīšana</option>
                    <option value="Skaistumkopšana">Skaistumkopšana</option>
                    <option value="Cits">Cits</option>
                </select>
            </div>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <font-awesome-icon icon="map-marker-alt" />
                </span>
                <input 
                    v-model="filters.city" 
                    type="text" 
                    placeholder="Pilsēta..." 
                    class="w-full pl-10 border p-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50 focus:bg-white transition"
                >
            </div>
        </div>
      </div>

      <div v-if="loading" class="text-center py-20 text-gray-400">
         <font-awesome-icon icon="circle-notch" spin class="text-3xl mb-2"/>
         <p>Meklējam darbus...</p>
      </div>

      <div v-else-if="filteredRequests.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="req in filteredRequests" :key="req.id" class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-6 border border-gray-100 flex flex-col h-full">
          
          <div class="flex justify-between items-start mb-3">
            <span class="bg-blue-50 text-blue-700 text-xs font-bold px-2 py-1 rounded uppercase tracking-wide">{{ req.category }}</span>
            <span class="text-green-600 font-bold flex items-center gap-1">
              <font-awesome-icon icon="euro-sign" class="text-sm"/> {{ req.budget }}
            </span>
          </div>

          <h3 class="text-lg font-bold text-gray-800 mb-2">{{ req.title }}</h3>
          <p class="text-gray-500 text-sm mb-4 line-clamp-3 flex-grow">{{ req.description }}</p>

          <div class="mt-auto pt-4 border-t border-gray-50">
            <div class="flex justify-between items-center text-xs text-gray-400 mb-4">
                <span class="flex items-center gap-1"><font-awesome-icon icon="map-marker-alt" /> {{ req.location }}</span>
                <span>{{ new Date(req.created_at).toLocaleDateString('lv-LV') }}</span>
            </div>

            <button 
                @click="openRequest(req)" 
                class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 transition"
            >
                Skatīt Vairāk
            </button>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
          <font-awesome-icon icon="search" class="text-4xl text-gray-300 mb-4" />
          <h3 class="text-xl font-bold text-gray-600">Nekas netika atrasts</h3>
          <p class="text-gray-400">Mēģiniet mainīt meklēšanas kritērijus.</p>
          <button @click="resetFilters" class="mt-4 text-blue-600 font-bold hover:underline">Notīrīt filtrus</button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

const requests = ref([]);
const loading = ref(true);
const router = useRouter();
const user = ref(null);

const filters = ref({
    search: '',
    category: '',
    city: ''
});

onMounted(async () => {
  const userData = localStorage.getItem('user');
  if (userData) user.value = JSON.parse(userData);

  try {
    const response = await fetch('api/get_requests.php');
    if (response.ok) {
      requests.value = await response.json();
    }
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
});

// Filtrēša
const filteredRequests = computed(() => {
    return requests.value.filter(req => {
        // Virsraksta
        const matchesSearch = req.title.toLowerCase().includes(filters.value.search.toLowerCase());
        
        //Kategorija
        const matchesCategory = filters.value.category ? req.category === filters.value.category : true;

        // Pilseta
        const matchesCity = req.location.toLowerCase().includes(filters.value.city.toLowerCase());

        return matchesSearch && matchesCategory && matchesCity;
    });
});

const resetFilters = () => {
    filters.value.search = '';
    filters.value.category = '';
    filters.value.city = '';
};

const openRequest = (req) => {
    // Īslaicīgs risinajums
    if (user.value && user.value.role === 'meistars') {
        applyForJob(req.id);
    } else {
        alert("Lai pieteiktos, jābūt Meistaram! Detalizēts skats drīz būs pieejams.");
    }
};

const applyForJob = async (id) => {
    const price = prompt("Jūsu cena (€):");
    if (!price) return;
    
    const comment = prompt("Komentārs klientam:");

    try {
        const response = await fetch('api/apply_for_job.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                request_id: id,
                master_id: user.value.id,
                price: price,
                comment: comment || ''
            })
        });
        if (response.ok) alert("Pieteikums nosūtīts!");
        else alert("Kļūda nosūtot pieteikumu.");
    } catch (e) { console.error(e); }
};
</script>