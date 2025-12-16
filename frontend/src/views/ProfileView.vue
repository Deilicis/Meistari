<template>
  <div class="min-h-screen bg-gray-100 py-10">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded shadow">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Mans Profils</h2>
        <button @click="$router.push('/dashboard')" class="bg-red-600 text-white font-bold p-1 rounded hover:bg-red-700 transition"><font-awesome-icon icon="times" /></button>
      </div>

      <div v-if="message" :class="`p-4 mb-4 rounded ${isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`">
        {{ message }}
      </div>

      <form @submit.prevent="saveProfile">
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-bold mb-1">Vārds</label>
            <input v-model="form.first_name" type="text" class="w-full border p-2 rounded">
          </div>
          <div>
            <label class="block text-sm font-bold mb-1">Uzvārds</label>
            <input v-model="form.last_name" type="text" class="w-full border p-2 rounded">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-bold mb-1">Specialitāte</label>
            <select v-model="form.specialty" class="w-full border p-2 rounded">
              <option value="">Izvēlieties...</option>
              <option value="Santehniķis">Santehniķis</option>
              <option value="Elektriķis">Elektriķis</option>
              <option value="Celtnieks">Celtnieks</option>
              <option value="IT Speciālists">IT Speciālists</option>
              <option value="Tīrīšana">Tīrīšana</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-bold mb-1">Pilsēta</label>
            <input v-model="form.city" type="text" class="w-full border p-2 rounded" placeholder="Rīga, Liepāja...">
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-bold mb-1">Telefona numurs</label>
          <input v-model="form.phone_number" type="text" class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
          <label class="block text-sm font-bold mb-1">Par sevi (Apraksts)</label>
          <textarea v-model="form.description" rows="3" class="w-full border p-2 rounded"></textarea>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-bold mb-1">Pieredze</label>
          <textarea v-model="form.experience" rows="3" class="w-full border p-2 rounded" placeholder="Kādi projekti veikti?"></textarea>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-bold">
          Saglabāt Izmaiņas
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const form = ref({
  first_name: '',
  last_name: '',
  specialty: '',
  city: '',
  phone_number: '',
  description: '',
  experience: ''
});

const message = ref('');
const isSuccess = ref(false);
const userId = ref(null);

onMounted(() => {

  const user = JSON.parse(localStorage.getItem('user'));
  if (user) {
    userId.value = user.id;
  }
});

const saveProfile = async () => {
  message.value = '';
  
  try {
    const response = await fetch('http://localhost/Meistari/api/update_profile.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: userId.value,
        ...form.value
      })
    });

    const data = await response.json();
    
    if (response.ok) {
      message.value = "Dati veiksmīgi saglabāti!";
      isSuccess.value = true;
    } else {
      message.value = "Kļūda: " + data.message;
      isSuccess.value = false;
    }
  } catch (error) {
    message.value = "Servera kļūda.";
    isSuccess.value = false;
  }
};
</script>