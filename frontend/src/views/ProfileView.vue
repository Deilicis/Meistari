<template>
  <div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-md border border-gray-100">
      
      <div class="flex justify-between items-center mb-8 border-b pb-4">
        <div>
           <h2 class="text-2xl font-bold text-gray-800">Profila Iestatījumi</h2>
           <p class="text-sm text-gray-500 mt-1">
             <span v-if="form.type === 'company'" class="text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded">Uzņēmuma Profils</span>
             <span v-else class="text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded">Meistara Profils</span>
           </p>
        </div>
        <button @click="$router.push('/dashboard')" class="bg-red-600 text-white font-bold p-1 rounded hover:bg-red-700 transition"><font-awesome-icon icon="times" />
        </button>
      </div>

      <div v-if="loading" class="text-center py-10 text-gray-400">
         <font-awesome-icon icon="circle-notch" spin class="text-2xl"/>
      </div>

      <form v-else @submit.prevent="saveProfile" class="space-y-6">
        
        <div v-if="form.type === 'company'" class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-blue-50 rounded-lg border border-blue-100">
             <div class="col-span-2">
                <h3 class="text-blue-800 font-bold text-sm uppercase tracking-wide mb-2">Juridiskā Informācija</h3>
             </div>
             <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Uzņēmuma nosaukums</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><font-awesome-icon icon="building" /></span>
                    <input v-model="form.company_name" type="text" class="w-full pl-10 border p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none" placeholder="SIA Būve">
                </div>
             </div>
             <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Reģistrācijas Numurs</label>
                <input v-model="form.reg_number" type="text" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
             </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Vārds</label>
            <input v-model="form.first_name" type="text" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Uzvārds</label>
            <input v-model="form.last_name" type="text" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-6">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Specialitāte</label>
            <select v-model="form.specialty" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none bg-white">
              <option value="">Izvēlieties...</option>
              <option value="Santehniķis">Santehniķis</option>
              <option value="Elektriķis">Elektriķis</option>
              <option value="Celtnieks">Celtnieks</option>
              <option value="IT Speciālists">IT Speciālists</option>
              <option value="Tīrīšana">Tīrīšana</option>
              <option value="Skaistumkopšana">Skaistumkopšana</option>
              <option value="Cits">Cits</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Pilsēta / Darbības Vieta</label>
            <input v-model="form.city" type="text" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Rīga, Liepāja...">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
           <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Telefona numurs</label>
              <input v-model="form.phone_number" type="text" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
           </div>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-1">Par sevi / Uzņēmumu</label>
          <textarea v-model="form.description" rows="4" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Īss apraksts par piedāvātajiem pakalpojumiem..."></textarea>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-1">Pieredze</label>
          <textarea v-model="form.experience" rows="2" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Kādi projekti veikti? Cik gadu pieredze?"></textarea>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-bold shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2">
               <font-awesome-icon icon="save" /> Saglabāt Izmaiņas
            </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNotifications } from '@/composables/useNotifications';

const { notify } = useNotifications();
const userId = ref(null);
const loading = ref(true);

// Noklusējuma forma
const form = ref({
  type: 'individual', // Noklusējums
  first_name: '',
  last_name: '',
  company_name: '',
  reg_number: '',
  specialty: '',
  city: '',
  phone_number: '',
  description: '',
  experience: ''
});

onMounted(async () => {
  const user = JSON.parse(localStorage.getItem('user'));
  if (user) {
    userId.value = user.id;
    await loadProfile();
  }
});

const loadProfile = async () => {
    try {
        const response = await fetch(`api/get_profile.php?id=${userId.value}`);
        if (response.ok) {
            const data = await response.json();
            // Apvienojam ielādētos datus ar formu. 
            // Ja datubāzē lauks ir null, tas paliek tukšs string no noklusējuma
            form.value = { ...form.value, ...data };
        }
    } catch (error) {
        console.error("Profile load error", error);
    } finally {
        loading.value = false;
    }
};

const saveProfile = async () => {
  try {
    const response = await fetch('api/update_profile.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: userId.value,
        ...form.value
      })
    });

    const data = await response.json();
    
    if (response.ok) {
      notify("Profils veiksmīgi saglabāts!", "success");
    } else {
      notify("Kļūda: " + data.message, "error");
    }
  } catch (error) {
    notify("Servera kļūda.", "error");
  }
};
</script>