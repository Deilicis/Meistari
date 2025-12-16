<template>
    <div class="min-h-screen bg-gray-50 py-10">
        <div class="max-w-6xl mx-auto px-4">

            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Meistaru Pakalpojumi</h2>
                    <p class="text-gray-500">Profesionāļi, kas gatavi palīdzēt.</p>
                </div>
                <button v-if="user && user.role === 'meistars'" @click="$router.push('/create-offer')"
                    class="bg-purple-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-purple-700 transition shadow-md">
                    <font-awesome-icon icon="plus-circle" /> Pievienot Pakalpojumu
                </button>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <div class="md:col-span-2 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><font-awesome-icon
                                icon="search" /></span>
                        <input v-model="filters.search" type="text" placeholder="Meklēt pakalpojumu..."
                            class="w-full pl-10 border p-2.5 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none bg-gray-50 focus:bg-white transition">
                    </div>

                    <div>
                        <select v-model="filters.category"
                            class="w-full border p-2.5 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none bg-gray-50 focus:bg-white transition text-gray-700">
                            <option value="">Visas kategorijas</option>
                            <option value="Santehnika">Santehnika</option>
                            <option value="Elektrība">Elektrība</option>
                            <option value="Celtniecība">Celtniecība</option>
                            <option value="IT Pakalpojumi">IT Pakalpojumi</option>
                            <option value="Tīrīšana">Tīrīšana</option>
                            <option value="Skaistumkopšana">Skaistumkopšana</option>
                            <option value="Cits">Cits</option>
                        </select>
                    </div>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><font-awesome-icon
                                icon="map-marker-alt" /></span>
                        <input v-model="filters.city" type="text" placeholder="Pilsēta..."
                            class="w-full pl-10 border p-2.5 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none bg-gray-50 focus:bg-white transition">
                    </div>
                </div>
            </div>

            <div v-if="loading" class="text-center py-20 text-gray-400">
                <font-awesome-icon icon="circle-notch" spin class="text-3xl mb-2" />
                <p>Meklējam meistarus...</p>
            </div>

            <div v-else-if="filteredOffers.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="offer in filteredOffers" :key="offer.id"
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-6 border border-gray-100 flex flex-col group h-full">
                    <div class="flex justify-between items-start mb-3">
                        <span
                            class="bg-purple-50 text-purple-700 text-xs font-bold px-2 py-1 rounded uppercase tracking-wide">{{
                            offer.category }}</span>
                        <span class="text-green-600 font-bold flex items-center gap-1">
                            <font-awesome-icon icon="euro-sign" class="text-sm" /> {{ offer.price }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-purple-600 transition">{{
                        offer.title }}</h3>
                    <p class="text-gray-500 text-sm mb-4 line-clamp-3 flex-grow">{{ offer.description }}</p>

                    <div class="mt-auto pt-4 border-t flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div @click.stop="openProfile(offer.user_id)"
                                class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs cursor-pointer hover:bg-purple-100 hover:text-purple-600 transition">
                                <font-awesome-icon icon="user" />
                            </div>
                            <div class="text-xs">
                                <p @click.stop="openProfile(offer.user_id)"
                                    class="font-bold text-gray-700 hover:text-blue-600 cursor-pointer transition">
                                    {{ getMasterName(offer) }}
                                </p>
                                <p class="text-gray-400" v-if="offer.city"><font-awesome-icon icon="map-marker-alt" />
                                    {{ offer.city }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
                <font-awesome-icon icon="hammer" class="text-4xl text-gray-300 mb-4" />
                <h3 class="text-xl font-bold text-gray-600">Neviens meistars netika atrasts</h3>
                <button @click="resetFilters" class="mt-4 text-purple-600 font-bold hover:underline">Notīrīt
                    filtrus</button>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router'

const router = useRouter();
const offers = ref([]);
const loading = ref(true);
const user = ref(null);

const filters = ref({ search: '', category: '', city: '' });

onMounted(async () => {
    const userData = localStorage.getItem('user');
    if (userData) user.value = JSON.parse(userData);

    try {
        const response = await fetch('api/get_offers.php'); // Šis fails jau eksistē no iepriekšējiem soļiem
        if (response.ok) offers.value = await response.json();
    } catch (e) { console.error(e); }
    finally { loading.value = false; }
});

const filteredOffers = computed(() => {
    return offers.value.filter(offer => {
        const matchesSearch = offer.title.toLowerCase().includes(filters.value.search.toLowerCase());
        const matchesCategory = filters.value.category ? offer.category === filters.value.category : true;
        // Pārbaudām pilsētu (tā nāk no master_profiles JOIN, api/get_offers.php to atgriež kā 'city')
        const offerCity = offer.city || '';
        const matchesCity = offerCity.toLowerCase().includes(filters.value.city.toLowerCase());

        return matchesSearch && matchesCategory && matchesCity;
    });
});

const resetFilters = () => {
    filters.value = { search: '', category: '', city: '' };
};

const getMasterName = (offer) => {
    if (offer.company_name) return offer.company_name;
    if (offer.first_name) return `${offer.first_name} ${offer.last_name}`;
    return offer.username;
};

const openProfile = (userId) => {
    router.push(`/master/${userId}`);
};
</script>