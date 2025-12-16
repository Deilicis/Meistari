<template>
  <div class="min-h-screen flex bg-gray-50">

    <div class="hidden lg:flex lg:w-1/2 bg-blue-900 text-white flex-col justify-center px-12 relative overflow-hidden">
      <div class="absolute top-0 left-0 w-full h-full opacity-10">
        <div class="absolute top-10 left-10 w-64 h-64 rounded-full bg-white blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 rounded-full bg-blue-400 blur-3xl"></div>
      </div>

      <div class="relative z-10 max-w-lg mx-auto">
        <div class="mb-6 text-blue-300 text-6xl">
          <font-awesome-icon icon="hammer" />
          <span class="text-1 font-extrabold text-white tracking-tight">
            Meistari<span class="text-blue-500">.lv</span>
          </span>
        </div>
        <h1 class="text-4xl font-extrabold mb-6 leading-tight">
          Pievienojies Latvijas lielākajai meistaru platformai.
        </h1>
        <p class="text-lg text-blue-200 mb-8 leading-relaxed">
          Atrodi darbu vai atrodi meistaru. Tagad arī uzņēmumiem.
        </p>
      </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-12">
      <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 lg:p-10 border border-gray-100">

        <div class="text-center mb-8">
          <h2 class="text-3xl font-bold text-gray-900">Izveidot kontu</h2>
          <p class="text-gray-500 mt-2">Izvēlieties savu lomu:</p>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-5">

          <div class="grid grid-cols-1 gap-3 mb-6">

            <div @click="setRole('mekletajs')"
              class="cursor-pointer border-2 rounded-xl p-4 flex items-center gap-4 transition-all duration-200 hover:shadow-md"
              :class="form.role === 'mekletajs' ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600' : 'border-gray-200 hover:border-blue-300'">
              <div class="w-10 h-10 rounded-full flex items-center justify-center bg-white border"
                :class="form.role === 'mekletajs' ? 'text-blue-600 border-blue-200' : 'text-gray-400 border-gray-200'">
                <font-awesome-icon icon="search" />
              </div>
              <div class="text-left">
                <p class="font-bold text-sm text-gray-900">Meklēju meistaru</p>
                <p class="text-xs text-gray-500">Esmu klients, vēlos publicēt darbus.</p>
              </div>
            </div>

            <div @click="setRole('meistars', 'individual')"
              class="cursor-pointer border-2 rounded-xl p-4 flex items-center gap-4 transition-all duration-200 hover:shadow-md"
              :class="(form.role === 'meistars' && form.master_type === 'individual') ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600' : 'border-gray-200 hover:border-blue-300'">
              <div class="w-10 h-10 rounded-full flex items-center justify-center bg-white border"
                :class="(form.role === 'meistars' && form.master_type === 'individual') ? 'text-blue-600 border-blue-200' : 'text-gray-400 border-gray-200'">
                <font-awesome-icon icon="user" />
              </div>
              <div class="text-left">
                <p class="font-bold text-sm text-gray-900">Esmu Meistars (Privātpersona)</p>
                <p class="text-xs text-gray-500">Strādāju individuāli (pašnodarbinātais).</p>
              </div>
            </div>

            <div @click="setRole('meistars', 'company')"
              class="cursor-pointer border-2 rounded-xl p-4 flex items-center gap-4 transition-all duration-200 hover:shadow-md"
              :class="(form.role === 'meistars' && form.master_type === 'company') ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-600' : 'border-gray-200 hover:border-blue-300'">
              <div class="w-10 h-10 rounded-full flex items-center justify-center bg-white border"
                :class="(form.role === 'meistars' && form.master_type === 'company') ? 'text-blue-600 border-blue-200' : 'text-gray-400 border-gray-200'">
                <font-awesome-icon icon="briefcase" />
              </div>
              <div class="text-left">
                <p class="font-bold text-sm text-gray-900">Esmu Uzņēmums</p>
                <p class="text-xs text-gray-500">SIA vai IK, piedāvājam pakalpojumus.</p>
              </div>
            </div>

          </div>

          <div class="space-y-4">

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ form.master_type === 'company' ? 'Uzņēmuma Nosaukums' : 'Lietotājvārds' }}
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <font-awesome-icon :icon="form.master_type === 'company' ? 'briefcase' : 'user'" />
                </span>
                <input v-model="form.username" type="text" required
                  class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                  :placeholder="form.master_type === 'company' ? 'SIA Būve' : 'JanisBerzins'">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">E-pasts</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <font-awesome-icon icon="envelope" />
                </span>
                <input v-model="form.email" type="email" required
                  class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                  placeholder="info@piemers.lv">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Parole</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <font-awesome-icon icon="lock" />
                </span>
                <input v-model="form.password" type="password" required
                  class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                  placeholder="••••••••">
              </div>
            </div>
          </div>

          <button type="submit"
            class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-lg hover:bg-blue-700 shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 mt-4">
            Reģistrēties
          </button>

        </form>

        <div class="mt-6 text-center border-t pt-6">
          <p class="text-sm text-gray-600">
            Jau ir konts?
            <router-link to="/login" class="text-blue-600 font-bold hover:underline">
              Pieslēgties šeit
            </router-link>
          </p>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useNotifications } from '@/composables/useNotifications';

const router = useRouter();
const { notify } = useNotifications();

const form = ref({
  username: '',
  email: '',
  password: '',
  role: 'mekletajs',
  master_type: 'individual' // Noklusējuma (tiek ignorēts, ja loma ir mekletajs)
});

// Palīgfunkcija lomu pārslēgšanai
const setRole = (role, type = 'individual') => {
  form.value.role = role;
  form.value.master_type = type;
};

const handleRegister = async () => {
  try {
    const response = await fetch('api/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)
    });

    const data = await response.json();

    if (response.ok) {
      notify('Reģistrācija veiksmīga! Lūdzu pieslēdzieties.', 'success');
      router.push('/login');
    } else {
      notify(data.message || 'Reģistrācijas kļūda.', 'error');
    }
  } catch (error) {
    notify('Servera kļūda. Mēģiniet vēlāk.', 'error');
  }
};
</script>