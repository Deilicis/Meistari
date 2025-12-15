<template>
  <div class="min-h-screen bg-gray-100 py-10">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow">
      
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Pievienot Pakalpojumu</h2>
        <button @click="$router.push('/dashboard')" class="text-gray-600 hover:text-gray-900">Atcelt</button>
      </div>

      <div v-if="message" :class="`p-4 mb-4 rounded ${isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`">
        {{ message }}
      </div>

      <form @submit.prevent="submitOffer">
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Pakalpojuma Nosaukums *</label>
          <input v-model="form.title" type="text" required placeholder="Piemēram: Datora pārinstalēšana" class="w-full border p-2 rounded">
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Kategorija *</label>
            <select v-model="form.category" required class="w-full border p-2 rounded">
              <option value="">Izvēlieties...</option>
              <option value="Santehnika">Santehnika</option>
              <option value="Elektrība">Elektrība</option>
              <option value="Celtniecība">Celtniecība</option>
              <option value="IT Pakalpojumi">IT Pakalpojumi</option>
              <option value="Tīrīšana">Tīrīšana</option>
              <option value="Skaistumkopšana">Skaistumkopšana</option>
              <option value="Cits">Cits</option>
            </select>
          </div>
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Pakalpojuma Veids *</label>
            <select v-model="form.service_type" required class="w-full border p-2 rounded">
              <option value="one_time">Vienreizējs darbs</option>
              <option value="long_term">Ilgtermiņa sadarbība</option>
              <option value="consultation">Konsultācija</option>
            </select>
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Cena (€) *</label>
          <input v-model="form.price" type="text" required placeholder="piem. 30.00 vai 20-50" class="w-full border p-2 rounded">
          <p class="text-xs text-gray-500 mt-1">Varat norādīt konkrētu summu vai diapazonu.</p>
        </div>

        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">Detalizēts Apraksts *</label>
          <textarea v-model="form.description" required rows="5" placeholder="Ko tieši Jūs piedāvājat? Kas ietilpst cenā?" class="w-full border p-2 rounded"></textarea>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700 transition flex justify-center items-center gap-2">
          <font-awesome-icon icon="plus-circle" /> Publicēt Pakalpojumu
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
  service_type: 'one_time',
  price: '',
  description: ''
});

const submitOffer = async () => {
  message.value = '';
  
  const user = JSON.parse(localStorage.getItem('user'));
  if (!user) {
    router.push('/login');
    return;
  }

  try {
    const response = await fetch('http://localhost/Meistari/api/create_offer.php', {
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
      message.value = "Pakalpojums veiksmīgi izveidots!";
      setTimeout(() => {
        router.push('/my-offers'); // Mēs tūlīt izveidosim šo lapu
      }, 1500);
    } else {
      isSuccess.value = false;
      message.value = data.message || "Kļūda sistēmā.";
    }
  } catch (error) {
    isSuccess.value = false;
    message.value = "Servera kļūda.";
  }
};
</script>