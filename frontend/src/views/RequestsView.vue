<template>
  <div class="min-h-screen bg-gray-100 py-10 relative">
    <div class="max-w-5xl mx-auto px-4">
      
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Aktīvie Darba Pieprasījumi</h2>
        <button @click="$router.push('/dashboard')" class="text-blue-600 hover:underline">Atpakaļ uz paneli</button>
      </div>

      <div class="bg-white p-4 rounded shadow mb-6">
        <input v-model="searchQuery" type="text" placeholder="Meklēt..." class="w-full border p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div v-if="loading" class="text-center text-gray-500 py-10">Ielādē...</div>
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="req in filteredRequests" :key="req.id" class="bg-white p-6 rounded-lg shadow flex flex-col">
          
          <div class="flex justify-between items-start mb-2">
            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ req.category }}</span>
            <span class="text-gray-500 text-sm">{{ formatDate(req.created_at) }}</span>
          </div>

          <h3 class="text-xl font-bold text-gray-900 mb-2">{{ req.title }}</h3>
          <p class="text-gray-600 text-sm mb-4 line-clamp-3 flex-grow">{{ req.description }}</p>

          <div class="border-t pt-4 mt-auto">
            <div class="flex justify-between items-center text-sm text-gray-700 mb-2">
              <span><font-awesome-icon icon="map-marker-alt" class="text-red-500" /> {{ req.location }}</span>
              <span class="font-bold text-green-600">Budžets: <font-awesome-icon icon="euro-sign" /> {{ req.budget }}</span>
            </div>
            
            <button @click="openModal(req)" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition font-medium">
              Pieteikties Darbam
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="selectedRequest" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        
        <h3 class="text-xl font-bold mb-4">Pieteikties: {{ selectedRequest.title }}</h3>
        
        <p class="text-gray-600 text-sm mb-4">
          Klieta budžets: <span class="font-bold">€ {{ selectedRequest.budget }}</span>
        </p>

        <form @submit.prevent="submitApplication">
          <div class="mb-4">
            <label class="block text-sm font-bold mb-1">Jūsu cena (€)</label>
            <input v-model="applicationForm.price" type="number" step="0.01" required class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500">
          </div>

          <div class="mb-4">
            <label class="block text-sm font-bold mb-1">Komentārs klientam</label>
            <textarea v-model="applicationForm.comment" rows="3" placeholder="Kad varat sākt? Kāpēc izvēlēties jūs?" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500"></textarea>
          </div>

          <div v-if="modalMessage" class="text-sm text-red-600 mb-4">{{ modalMessage }}</div>

          <div class="flex justify-end gap-2">
            <button type="button" @click="closeModal" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Atcelt</button>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white font-bold rounded hover:bg-green-700">Nosūtīt</button>
          </div>
        </form>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const requests = ref([]);
const loading = ref(true);
const searchQuery = ref('');

// Modāļa mainīgie
const selectedRequest = ref(null); // Kurš darbs ir atvērts
const applicationForm = ref({ price: '', comment: '' });
const modalMessage = ref('');
const user = ref(null);

onMounted(async () => {
  // Ielādējam useri, lai zinātu ID
  const userData = localStorage.getItem('user');
  if (userData) user.value = JSON.parse(userData);

  try {
    const response = await fetch('http://localhost/Meistari/api/get_requests.php');
    if (response.ok) {
      requests.value = await response.json();
    }
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
});

const filteredRequests = computed(() => {
  if (!searchQuery.value) return requests.value;
  const lower = searchQuery.value.toLowerCase();
  return requests.value.filter(req => req.title.toLowerCase().includes(lower) || req.category.toLowerCase().includes(lower));
});

const formatDate = (d) => new Date(d).toLocaleDateString('lv-LV');

// --- MODĀĻA LOĢIKA ---

const openModal = (req) => {
  selectedRequest.value = req;
  modalMessage.value = '';
  // Iestatām cenu automātiski uz budžeta sākumu (kā ieteikumu)
  applicationForm.value.price = ''; 
  applicationForm.value.comment = '';
};

const closeModal = () => {
  selectedRequest.value = null;
};

const submitApplication = async () => {
  if (!user.value) {
    modalMessage.value = "Lūdzu vispirms ielogojieties!";
    return;
  }

  try {
    const response = await fetch('http://localhost/Meistari/api/apply_for_job.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        request_id: selectedRequest.value.id,
        master_id: user.value.id,
        price: applicationForm.value.price,
        comment: applicationForm.value.comment
      })
    });

    const data = await response.json();

    if (response.ok) {
      alert("Pieteikums veiksmīgi nosūtīts!"); // Vienkāršs paziņojums
      closeModal();
    } else {
      modalMessage.value = data.message;
    }
  } catch (error) {
    modalMessage.value = "Servera kļūda.";
  }
};
</script>