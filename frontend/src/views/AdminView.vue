<template>
  <div class="min-h-screen bg-gray-100 flex">
    
    <aside class="w-64 bg-blue-900 text-white min-h-screen hidden md:block">
      <div class="p-6 text-2xl font-bold flex items-center gap-2">
        <font-awesome-icon icon="user-cog" />
        Admin
      </div>
      <nav class="mt-6">
        <a href="#" class="block py-3 px-6 bg-blue-800 border-l-4 border-blue-400">
          <font-awesome-icon icon="user" class="mr-2"/> Lietotāji
        </a>
        <a href="#" class="block py-3 px-6 hover:bg-blue-800 text-blue-200">
          <font-awesome-icon icon="briefcase" class="mr-2"/> Visi Darbi
        </a>
      </nav>
    </aside>

    <main class="flex-1 p-8">
      
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Lietotāju Pārvaldība</h2>
        <button @click="$router.push('/dashboard')" class="text-blue-600 hover:underline flex items-center gap-2">
           <font-awesome-icon icon="sign-out-alt" /> Iziet no Admin
        </button>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full leading-normal">
          <thead>
            <tr>
              <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                ID
              </th>
              <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Lietotājs
              </th>
              <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Loma
              </th>
              <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Reģistrēts
              </th>
              <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Darbības
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50">
              <td class="px-5 py-5 border-b border-gray-200 text-sm">{{ u.id }}</td>
              <td class="px-5 py-5 border-b border-gray-200 text-sm">
                <div class="flex items-center">
                  <div class="ml-3">
                    <p class="text-gray-900 whitespace-no-wrap font-bold">{{ u.username }}</p>
                    <p class="text-gray-600 whitespace-no-wrap">{{ u.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-5 border-b border-gray-200 text-sm">
                <span 
                  class="relative inline-block px-3 py-1 font-semibold leading-tight rounded-full"
                  :class="{
                    'bg-green-200 text-green-900': u.role === 'mekletajs',
                    'bg-blue-200 text-blue-900': u.role === 'meistars',
                    'bg-red-200 text-red-900': u.role === 'admin'
                  }"
                >
                  {{ u.role }}
                </span>
              </td>
              <td class="px-5 py-5 border-b border-gray-200 text-sm">
                {{ new Date(u.created_at).toLocaleDateString('lv-LV') }}
              </td>
              <td class="px-5 py-5 border-b border-gray-200 text-sm text-right">
                <button 
                  v-if="u.role !== 'admin'"
                  @click="deleteUser(u.id)"
                  class="text-red-600 hover:text-red-900 font-bold"
                  title="Dzēst lietotāju"
                >
                  <font-awesome-icon icon="trash" /> Dzēst
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

// Lai ikona "trash" strādātu, tā jāimportē main.js, bet pagaidām izmantosim tekstu vai pievienosim to tur.
// Es pieņemu, ka trash ikona būs pieejama.

const users = ref([]);
const router = useRouter();

const fetchUsers = async () => {
  try {
    const response = await fetch('http://localhost/Meistari/api/admin_get_users.php');
    if (response.ok) {
      users.value = await response.json();
    }
  } catch (error) {
    console.error(error);
  }
};

const deleteUser = async (id) => {
  if (!confirm('Vai tiešām vēlaties dzēst šo lietotāju? Šī darbība ir neatgriezeniska.')) return;

  try {
    const response = await fetch('http://localhost/Meistari/api/admin_delete_user.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });

    if (response.ok) {
      // Atjaunojam sarakstu
      users.value = users.value.filter(u => u.id !== id);
      alert('Lietotājs izdzēsts.');
    } else {
      alert('Kļūda dzēšot.');
    }
  } catch (error) {
    console.error(error);
  }
};

onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user'));
  // Vienkārša aizsardzība frontend pusē
  if (!user || user.role !== 'admin') {
    router.push('/dashboard');
    return;
  }
  fetchUsers();
});
</script>