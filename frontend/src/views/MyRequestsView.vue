<template>
  <div class="min-h-screen bg-gray-100 py-10">
    <div class="max-w-4xl mx-auto px-4">
      
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Mani Izsludinātie Darbi</h2>
        <button @click="$router.push('/dashboard')" class="text-blue-600 hover:underline">Atpakaļ uz paneli</button>
      </div>

      <div v-if="loading" class="text-center text-gray-500">Ielādē datus...</div>
      
      <div v-else-if="requests.length === 0" class="text-center bg-white p-8 rounded shadow">
        <p class="text-gray-600 mb-4">Jums vēl nav aktīvu darba sludinājumu.</p>
        <button @click="$router.push('/create-request')" class="bg-green-600 text-white px-4 py-2 rounded font-bold">
          Izveidot Pirmo
        </button>
      </div>

      <div v-else class="space-y-6">
        <div v-for="req in requests" :key="req.id" class="bg-white rounded-lg shadow overflow-hidden">
          
          <div class="p-6 border-b flex justify-between items-start">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded">{{ req.category }}</span>
                <span class="text-gray-400 text-sm">{{ formatDate(req.created_at) }}</span>
              </div>
              <h3 class="text-xl font-bold text-gray-900">{{ req.title }}</h3>
              <p class="text-gray-600 mt-2 text-sm">{{ req.description }}</p>
            </div>
            <div class="text-right">
              <div class="font-bold text-green-600 mb-2">Budžets: €{{ req.budget }}</div>
              
              <button 
                @click="toggleApplicants(req.id)"
                class="flex items-center gap-2 text-sm font-medium px-4 py-2 rounded transition"
                :class="activeRequest === req.id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              >
                <span>👤 {{ req.applicant_count }} Pieteikumi</span>
                <span v-if="activeRequest === req.id">▼</span>
                <span v-else>▶</span>
              </button>
            </div>
          </div>

          <div v-if="activeRequest === req.id" class="bg-gray-50 p-6 border-t border-gray-200">
            <h4 class="font-bold text-gray-700 mb-4">Meistaru piedāvājumi:</h4>
            
            <div v-if="applicantsLoading" class="text-sm text-gray-500">Ielādē kandidātus...</div>
            
            <div v-else-if="applicants.length === 0" class="text-sm text-gray-500 italic">
              Šim darbam vēl neviens nav pieteicies.
            </div>

            <div v-else class="space-y-3">
              <div v-for="app in applicants" :key="app.id" class="bg-white p-4 rounded border flex justify-between items-center">
                
                <div>
                  <div class="font-bold text-gray-800">
                    {{ getMasterName(app) }}
                    <span class="text-xs font-normal text-gray-500">(@{{ app.username }})</span>
                  </div>
                  <p class="text-gray-600 text-sm mt-1">"{{ app.comment }}"</p>
                </div>

                <div class="text-right">
                  <div class="text-lg font-bold text-blue-600">€ {{ app.price }}</div>
                  <button class="text-xs text-green-600 hover:underline mt-1 font-medium">Apstiprināt</button>
                </div>

              </div>
            </div>

          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const requests = ref([]);
const applicants = ref([]);
const loading = ref(true);
const applicantsLoading = ref(false);
const activeRequest = ref(null); // ID atvērtajam darbam

onMounted(async () => {
  const user = JSON.parse(localStorage.getItem('user'));
  if (!user) return;

  try {
    const response = await fetch(`http://localhost/Meistari/api/get_my_requests.php?id=${user.id}`);
    if (response.ok) {
      requests.value = await response.json();
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
});

// Funkcija, kas ielādē kandidātus konkrētam darbam
const toggleApplicants = async (requestId) => {
  if (activeRequest.value === requestId) {
    activeRequest.value = null; // Aizvērt, ja jau atvērts
    return;
  }

  activeRequest.value = requestId;
  applicantsLoading.value = true;
  applicants.value = []; // Notīrīt vecos

  try {
    const response = await fetch(`http://localhost/Meistari/api/get_applicants.php?request_id=${requestId}`);
    if (response.ok) {
      applicants.value = await response.json();
    }
  } catch (e) {
    console.error(e);
  } finally {
    applicantsLoading.value = false;
  }
};

const getMasterName = (app) => {
  if (app.type === 'company') return app.company_name;
  if (app.first_name) return `${app.first_name} ${app.last_name}`;
  return 'Nezināms Meistars'; // Ja profils nav aizpildīts
};

const formatDate = (d) => new Date(d).toLocaleDateString('lv-LV');
</script>