<template>
  <div class="min-h-screen bg-gray-100 py-10">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow">
      
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Jauns Pieprasījums</h2>
        <button @click="$router.push('/dashboard')" class="bg-red-600 text-white font-bold p-1 rounded hover:bg-red-700 transition"><font-awesome-icon icon="times" /></button>
      </div>

      <div v-if="message" :class="`p-4 mb-4 rounded ${isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`">
        {{ message }}
      </div>

      <form @submit.prevent="submitRequest">
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Virsraksts *</label>
          <input v-model="form.title" type="text" required placeholder="Piemēram: Vajag salabot pilošu krānu" class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Kategorija *</label>
          <select v-model="form.category" required class="w-full border p-2 rounded">
            <option value="">Izvēlieties kategoriju...</option>
            <option value="Santehnika">Santehnika</option>
            <option value="Elektrība">Elektrība</option>
            <option value="Celtniecība">Celtniecība</option>
            <option value="IT Pakalpojumi">IT Pakalpojumi</option>
            <option value="Tīrīšana">Tīrīšana</option>
            <option value="Cits">Cits</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Atrašanās vieta *</label>
            <input v-model="form.location" type="text" required placeholder="Rīga, Centrs" class="w-full border p-2 rounded">
          </div>
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Budžets (€) *</label>
            <input v-model="form.budget" type="text" required placeholder="50-100" class="w-full border p-2 rounded">
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Vēlamais Termiņš</label>
          <input v-model="form.deadline" type="date" class="w-full border p-2 rounded">
        </div>

        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">Detalizēts Apraksts *</label>
          <textarea v-model="form.description" required rows="5" placeholder="Aprakstiet darbu detalizēti..." class="w-full border p-2 rounded"></textarea>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700 transition">
          Publicēt Pieprasījumu
        </button>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const message = ref('');
const isSuccess = ref(false);

const form = ref({
  title: '',
  category: '',
  location: '',
  budget: '',
  deadline: '',
  description: ''
});

const submitRequest = async () => {
  message.value = '';
  const user = JSON.parse(localStorage.getItem('user'));
  if (!user) {
    router.push('/login');
    return;
  }

  try {
    const response = await fetch('http://localhost/Meistari/api/create_request.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: user.id,
        ...form.value
      })
    });

    const data = await response.json();

    if (response.ok) {
      isSuccess.value = true;
      message.value = "Pieprasījums veiksmīgi publicēts!";
      // Pēc 1.5 sekundes delay
      setTimeout(() => {
        router.push('/dashboard');
      }, 1500);
    } else {
      isSuccess.value = false;
      message.value = data.message || "Kļūda sistēmā.";
    }
  } catch (error) {
    isSuccess.value = false;
    message.value = "Servera kļūda.";
    console.error(error);
  }
};
</script>