<template>

  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
      <div class="flex justify-center items-center gap-1 p-4 cursor-pointer" @click="$router.push('/')">
        <font-awesome-icon icon="hammer" class="text-blue-600 text-2xl" />
        <span class="text-3xl font-extrabold text-blue-900 tracking-tight">Meistari<span
            class="text-blue-500">.lv</span></span>
      </div>
      <h2 class="text-2xl font-bold mb-6 text-center text-blue-900 tracking-tight">Pieslēgties</h2>
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
import { useNotifications } from '@/composables/useNotifications';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const { notify } = useNotifications();
const email = ref('');
const password = ref('');
const errorMessage = ref('');
const router = useRouter();

// 2. The Login Function
const handleLogin = async () => {
  errorMessage.value = '';

  try {

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
      console.log('Login izdevās:', data.user);

      const sessionDuration = 2 * 60 * 60 * 1000;
      const sessionData = {
        ...data.user,
        expiry: new Date().getTime() + sessionDuration
      };

      localStorage.setItem('user', JSON.stringify(sessionData));
      notify(`Sveicināti atpakaļ, ${data.user.username}!`, 'success');
      router.push('/dashboard');
    } else {
      notify(data.message || 'Kļūda sistēmā.', 'error');
    }

  } catch (error) {
    notify('Nevarēja savienoties ar serveri.', 'error');
    console.error(error);
  }
};
</script>