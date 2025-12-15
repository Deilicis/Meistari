<template>
  <div class="min-h-screen bg-gray-100 py-10">
    <div class="max-w-5xl mx-auto px-4">
      
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Mani Pakalpojumi</h2>
        <div class="flex gap-4">
            <button @click="$router.push('/create-offer')" class="bg-green-600 text-white px-4 py-2 rounded font-bold hover:bg-green-700 transition flex items-center gap-2">
                <font-awesome-icon icon="plus-circle" /> Pievienot Jaunu
            </button>
            <button @click="$router.push('/dashboard')" class="text-blue-600 hover:underline">Atpakaļ uz paneli</button>
        </div>
      </div>

      <div v-if="loading" class="text-center text-gray-500 py-10">Ielādē datus...</div>

      <div v-else-if="offers.length === 0" class="text-center bg-white p-10 rounded shadow">
        <p class="text-gray-600 mb-4 text-lg">Jums vēl nav aktīvu pakalpojumu.</p>
        <p class="text-gray-500 text-sm mb-6">Izveidojiet savu pirmo pakalpojumu, lai klienti varētu Jūs atrast!</p>
        <button @click="$router.push('/create-offer')" class="bg-blue-600 text-white px-6 py-3 rounded font-bold hover:bg-blue-700">
          Izveidot Pakalpojumu
        </button>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="offer in offers" :key="offer.id" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition flex flex-col">
          
          <div class="flex justify-between items-start mb-2">
            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded uppercase">{{ offer.category }}</span>
            <span class="text-gray-400 text-xs bg-gray-100 px-2 py-1 rounded">{{ translateType(offer.service_type) }}</span>
          </div>

          <h3 class="text-xl font-bold text-gray-900 mb-2">{{ offer.title }}</h3>
          <p class="text-gray-600 text-sm mb-4 line-clamp-3 flex-grow">{{ offer.description }}</p>

          <div class="border-t pt-4 mt-auto flex justify-between items-center">
            <span class="font-bold text-green-600 text-lg">
                <font-awesome-icon icon="euro-sign" /> {{ offer.price }}
            </span>
            <button class="text-gray-400 hover:text-red-500 text-sm">
                <font-awesome-icon icon="trash" /> Dzēst
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const offers = ref([]);
const loading = ref(true);

onMounted(async () => {
  const user = JSON.parse(localStorage.getItem('user'));
  if (!user) return;

  try {
    const response = await fetch(`http://localhost/Meistari/api/get_my_offers.php?id=${user.id}`);
    if (response.ok) {
      offers.value = await response.json();
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
});

const translateType = (type) => {
    const types = {
        'one_time': 'Vienreizējs',
        'long_term': 'Ilgtermiņa',
        'consultation': 'Konsultācija'
    };
    return types[type] || type;
};
</script>