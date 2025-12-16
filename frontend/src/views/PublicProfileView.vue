<template>
  <div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-5xl mx-auto px-4">

      <div v-if="loading" class="text-center py-20 text-gray-400">
         <font-awesome-icon icon="circle-notch" spin class="text-3xl mb-2"/>
         <p>Ielādē profilu...</p>
      </div>

      <div v-else-if="profile" class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        
        <div class="bg-blue-900 p-8 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-60 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
            
            <button @click="$router.go(-1)" class="text-white font-bold p-1 rounded hover:bg-white hover:text-red-800 transition"><font-awesome-icon icon="times" />
            </button>

            <div class="flex flex-col md:flex-row items-start md:items-center gap-6 relative z-10">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center text-blue-900 text-4xl shadow-lg border-4 border-blue-800">
                    <font-awesome-icon :icon="profile.type === 'company' ? 'building' : 'user'" />
                </div>
                
                <div>
                    <h1 class="text-3xl font-bold mb-1">{{ getDisplayName(profile) }}</h1>
                    <div class="flex flex-wrap gap-2 text-blue-200 text-sm">
                        <span class="bg-blue-800 px-3 py-1 rounded-full text-white font-medium flex items-center gap-2">
                             <font-awesome-icon icon="hammer" /> {{ profile.specialty || 'Nav norādīts' }}
                        </span>
                        <span v-if="profile.city" class="flex items-center gap-1">
                             <font-awesome-icon icon="map-marker-alt" /> {{ profile.city }}
                        </span>
                        <span v-if="profile.type === 'company'" class="flex items-center gap-1 border-l border-blue-700 pl-2 ml-1">
                             <font-awesome-icon icon="briefcase" /> Uzņēmums
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="md:col-span-2 space-y-8">
                
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Par Meistaru</h3>
                    <p class="text-gray-600 leading-relaxed whitespace-pre-line">
                        {{ profile.description || 'Apraksts nav pievienots.' }}
                    </p>
                </div>

                <div v-if="profile.experience">
                    <h3 class="text-lg font-bold text-gray-800 mb-3 border-b pb-2">Pieredze</h3>
                    <p class="text-gray-600 leading-relaxed">
                        {{ profile.experience }}
                    </p>
                </div>

            </div>

            <div class="bg-gray-50 p-6 rounded-xl h-fit border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Kontaktinformācija</h3>
                
                <div class="space-y-4">
                    <div v-if="profile.phone_number" class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 mt-1">
                            <font-awesome-icon icon="phone" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Telefons</p>
                            <a :href="`tel:${profile.phone_number}`" class="text-gray-800 font-medium hover:text-blue-600 transition">{{ profile.phone_number }}</a>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mt-1">
                            <font-awesome-icon icon="envelope" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">E-pasts</p>
                            <a :href="`mailto:${profile.email}`" class="text-gray-800 font-medium hover:text-blue-600 transition break-all">{{ profile.email }}</a>
                        </div>
                    </div>
                </div>

                <div v-if="profile.type === 'company' && profile.reg_number" class="mt-6 pt-6 border-t border-gray-200">
                     <p class="text-xs text-gray-500">Reģ. Nr: <span class="text-gray-700 font-mono">{{ profile.reg_number }}</span></p>
                </div>

                <div class="mt-8">
                     <button class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 transition shadow-md">
                        Sazināties
                     </button>
                </div>
            </div>

        </div>

      </div>

      <div v-else class="text-center py-20">
          <h2 class="text-2xl font-bold text-gray-400">Profils nav atrasts</h2>
          <button @click="$router.push('/offers')" class="mt-4 text-blue-600 hover:underline">Atgriezties pie saraksta</button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const profile = ref(null);
const loading = ref(true);

onMounted(async () => {
    const id = route.params.id;
    
    try {
        const response = await fetch(`/api/get_public_profile.php?id=${id}`);
        if (response.ok) {
            profile.value = await response.json();
        }
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
});

const getDisplayName = (p) => {
    if (p.type === 'company') return p.company_name;
    if (p.first_name && p.last_name) return `${p.first_name} ${p.last_name}`;
    return p.username;
};
</script>