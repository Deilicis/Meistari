<template>
  <div class="min-h-screen bg-gray-100 py-10">
    <div class="max-w-5xl mx-auto px-4">
      
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Mani Pieteikumi</h2>
        <button @click="$router.push('/dashboard')" class="bg-red-600 text-white font-bold p-1 rounded hover:bg-red-700 transition"><font-awesome-icon icon="times" />
        </button>
      </div>

      <div v-if="loading" class="text-center py-10 text-gray-400">
         <font-awesome-icon icon="circle-notch" spin class="text-2xl"/> Ielādē datus...
      </div>

      <div v-else-if="apps.length === 0" class="text-center bg-white p-10 rounded shadow">
        <p class="text-gray-600 mb-4 text-lg">Jūs vēl neesat pieteicies nevienam darbam.</p>
        <button @click="$router.push('/requests')" class="bg-blue-600 text-white px-6 py-3 rounded font-bold hover:bg-blue-700">
          Meklēt Darbus
        </button>
      </div>

      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full leading-normal">
            <thead>
              <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                  Datums
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                  Darba Nosaukums
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                  Piedāvātā Cena
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                  Statuss
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="app in apps" :key="app.id" class="hover:bg-gray-50 transition">
                <td class="px-5 py-5 border-b border-gray-200 text-sm text-gray-500">
                  {{ new Date(app.created_at).toLocaleDateString('lv-LV') }}
                </td>
                <td class="px-5 py-5 border-b border-gray-200 text-sm font-bold text-gray-800">
                  {{ app.request_title }}
                  <span v-if="app.request_status === 'completed' && app.status !== 'accepted'" class="ml-2 text-xs text-red-500 font-normal">(Darbs noslēgts)</span>
                </td>
                <td class="px-5 py-5 border-b border-gray-200 text-sm font-bold text-green-600">
                  € {{ app.price }}
                </td>
                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                  <span class="px-3 py-1 rounded-full text-xs font-bold" :class="getStatusClass(app.status)">
                    {{ translateStatus(app.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNotifications } from '@/composables/useNotifications'; 

const apps = ref([]);
const loading = ref(true);

onMounted(async () => {
  const user = JSON.parse(localStorage.getItem('user'));
  if (!user) return;

  try {
    const response = await fetch(`api/get_my_applications.php?id=${user.id}`);
    if (response.ok) {
      apps.value = await response.json();
    }
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
});

const getStatusClass = (status) => {
    switch(status) {
        case 'accepted': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-yellow-100 text-yellow-800';
    }
};

const translateStatus = (status) => {
    switch(status) {
        case 'accepted': return 'Apstiprināts';
        case 'rejected': return 'Noraidīts';
        case 'pending': return 'Gaida atbildi';
        default: return status;
    }
};
</script>