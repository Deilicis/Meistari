<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 py-10">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
      <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Reģistrācija</h2>

      <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
        {{ errorMessage }}
      </div>
      <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
        {{ successMessage }}
      </div>

      <form @submit.prevent="handleRegister">
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Lietotājvārds</label>
          <input 
            v-model="username" 
            type="text" 
            required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="JānisB"
          >
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">E-pasts</label>
          <input 
            v-model="email" 
            type="email" 
            required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="janis@piemers.lv"
          >
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Es vēlos:</label>
          <div class="flex gap-4">
            <label class="flex items-center cursor-pointer">
              <input type="radio" value="mekletajs" v-model="role" class="mr-2">
              <span class="text-gray-800">Meklēt meistarus</span>
            </label>
            <label class="flex items-center cursor-pointer">
              <input type="radio" value="meistars" v-model="role" class="mr-2">
              <span class="text-gray-800">Piedāvāt pakalpojumus</span>
            </label>
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Parole</label>
          <input 
            v-model="password" 
            type="password" 
            required
            minlength="8"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Vismaz 8 simboli"
          >
        </div>

        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">Atkārtot Paroli</label>
          <input 
            v-model="confirmPassword" 
            type="password" 
            required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="********"
          >
        </div>

        <button 
          type="submit" 
          class="w-full bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700 transition duration-200"
        >
          Reģistrēties
        </button>
      </form>

      <p class="mt-4 text-center text-sm text-gray-600">
        Jau ir konts? 
        <RouterLink to="/login" class="text-blue-600 hover:underline">Pieslēgties</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const username = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const role = ref('mekletajs'); // Default selection
const errorMessage = ref('');
const successMessage = ref('');
const router = useRouter();

const handleRegister = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  // 1. Client-side Validation
  if (password.value !== confirmPassword.value) {
    errorMessage.value = "Paroles nesakrīt!";
    return;
  }
  if (password.value.length < 8) {
    errorMessage.value = "Parolei jābūt vismaz 8 simbolus garai.";
    return;
  }

  try {
    // 2. Send Data to API
    const response = await fetch('http://localhost/Meistari/api/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: username.value,
        email: email.value,
        password: password.value,
        role: role.value
      })
    });

    const data = await response.json();

    if (response.ok) {
      successMessage.value = "Reģistrācija veiksmīga! Pārvirza uz pieslēgšanos...";
      // Wait 2 seconds so user sees the success message, then redirect
      setTimeout(() => {
        router.push('/login');
      }, 2000);
    } else {
      errorMessage.value = data.message || "Reģistrācijas kļūda.";
    }

  } catch (error) {
    errorMessage.value = "Nevarēja savienoties ar serveri.";
    console.error(error);
  }
};
</script>