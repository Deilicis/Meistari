<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-2">
                        <font-awesome-icon icon="hammer" class="text-blue-600 text-xl" />
                        <h1 class="text-xl font-bold text-blue-600">Meistari.lv</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <span v-if="user" class="text-gray-700 flex items-center gap-2">
                            <font-awesome-icon icon="user" class="text-gray-400" />
                            {{ user.username }} ({{ user.role }})
                        </span>
                        <button @click="logout" class="text-red-600 hover:text-red-800 font-medium flex items-center gap-2">
                            <font-awesome-icon icon="sign-out-alt" /> Iziet
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

            <div v-if="!user" class="text-center text-gray-500">Ielādē datus...</div>

            <div v-else-if="user.role === 'mekletajs'" class="space-y-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Meklētāja Panelis</h2>
                    <p class="text-gray-600 mb-6">Pārvaldiet savus darbus un atrodiet meistarus.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border p-6 rounded-lg hover:bg-gray-50 cursor-pointer transition flex items-start gap-4"
                            @click="$router.push('/my-requests')">
                            <div class="bg-blue-100 p-3 rounded-full">
                                <font-awesome-icon icon="briefcase" class="text-blue-600 text-xl" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-blue-600">Mani Darbi</h3>
                                <p class="text-sm text-gray-500">Skatīt statusu un pieteikumus.</p>
                            </div>
                        </div>
                        <div class="border p-6 rounded-lg hover:bg-gray-50 cursor-pointer transition flex items-start gap-4"
                            @click="$router.push('/create-request')">
                            <div class="bg-green-100 p-3 rounded-full">
                                <font-awesome-icon icon="plus-circle" class="text-green-600 text-xl" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-green-600">Izveidot Pieprasījumu</h3>
                                <p class="text-sm text-gray-500">Publicēt jaunu darba sludinājumu.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="user.role === 'meistars'" class="space-y-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Meistara Panelis</h2>
                    <p class="text-gray-600 mb-6">Pārvaldiet savus piedāvājumus un meklējiet jaunus klientus.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border p-6 rounded-lg hover:bg-gray-50 cursor-pointer transition flex items-start gap-4"
                            @click="$router.push('/requests')">
                            <div class="bg-blue-100 p-3 rounded-full">
                                <font-awesome-icon icon="search" class="text-blue-600 text-xl" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-blue-600">Meklēt Darbus</h3>
                                <p class="text-sm text-gray-500">Skatīt klientu pieprasījumus.</p>
                            </div>
                        </div>
                        <div class="border p-6 rounded-lg hover:bg-gray-50 cursor-pointer transition flex items-start gap-4"
                            @click="$router.push('/profile')">
                            <div class="bg-green-100 p-3 rounded-full">
                                <font-awesome-icon icon="user-cog" class="text-green-600 text-xl" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-green-600">Mans Profils</h3>
                                <p class="text-sm text-gray-500">Rediģēt pieredzi un portfolio.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const user = ref(null);
const router = useRouter();

onMounted(() => {
    const userData = localStorage.getItem('user');
    if (!userData) {
        router.push('/login');
    } else {
        user.value = JSON.parse(userData);
    }
});

const logout = () => {
    localStorage.removeItem('user');
    router.push('/login');
};
</script>