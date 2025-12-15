<template>

  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
      <div class="flex justify-center items-center gap-1 p-4 cursor-pointer" @click="$router.push('/')">
        <font-awesome-icon icon="hammer" class="text-blue-600 text-2xl" />
        <span class="text-3xl font-extrabold text-blue-900 tracking-tight">Meistari<span
            class="text-blue-500">.lv</span></span>
      </div>
      <h2 class="text-2xl font-bold mb-6 text-center text-blue-900 tracking-tight">Pieslēgties</h2>

      <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin">

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">E-pasts</label>
          <input v-model="email" type="email" required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="janis@example.com">
        </div>

        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">Parole</label>
          <input v-model="password" type="password" required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="********">
        </div>

        <button type="submit"
          class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
          Pieslēgties
        </button>
      </form>

      <p class="mt-4 text-center text-sm text-gray-600">
        Nav konta?
        <RouterLink to="/register" class="text-blue-600 hover:underline">Reģistrēties</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

// 1. Variables to store input data
const email = ref('');
const password = ref('');
const errorMessage = ref('');
const router = useRouter(); // To redirect user after login

// 2. The Login Function
const handleLogin = async () => {
  errorMessage.value = ''; // Clear previous errors

  try {
    // Send data to your PHP API
    const response = await fetch('http://localhost/Meistari/api/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: email.value,
        password: password.value
      })
    });

    const data = await response.json();

    if (response.ok) {
      // SUCCESS: Login worked
      console.log('Login successful:', data.user);

      const sessionDuration = 2 * 60 * 60 * 1000;
      const sessionData = {
        ...data.user,
        expiry: new Date().getTime() + sessionDuration
      };

      localStorage.setItem('user', JSON.stringify(sessionData));

      // Redirect to Dashboard (we will create this later)
      router.push('/dashboard');
    } else {
      // ERROR: Show the message from PHP (e.g., "Nepareiza parole")
      errorMessage.value = data.message || 'Kļūda sistēmā.';
    }

  } catch (error) {
    errorMessage.value = 'Nevarēja savienoties ar serveri.';
    console.error(error);
  }
};
</script>