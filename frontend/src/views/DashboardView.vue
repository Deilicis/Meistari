<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-blue-600">Meistari.lv</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <span v-if="user" class="text-gray-700">Sveiki, {{ user.username }}! ({{ user.role }})</span>
                        <button @click="logout" class="text-red-600 hover:text-red-800 font-medium">Iziet</button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

            <div v-if="!user" class="text-center text-gray-500">Ielādē datus...</div>

            <div v-else-if="user.role === 'mekletajs'" class="space-y-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-2xl font-bold mb-4">Meklētāja Panelis</h2>
                    <p class="text-gray-600 mb-6">Šeit jūs varat meklēt meistarus vai pievienot savus darba
                        pieprasījumus.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border p-4 rounded hover:bg-gray-50 cursor-pointer"
                            @click="$router.push('/my-requests')">
                            <h3 class="font-bold text-lg text-blue-600">Mani Darbi</h3>
                            <p class="text-sm text-gray-500">Skatīt statusu un pieteikumus.</p>
                        </div>
                        <div class="border p-4 rounded hover:bg-gray-50 cursor-pointer"
                            @click="$router.push('/create-request')">
                            <h3 class="font-bold text-lg text-green-600">Izveidot Pieprasījumu</h3>
                            <p class="text-sm text-gray-500">Publicēt jaunu darba sludinājumu.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="user.role === 'meistars'" class="space-y-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-2xl font-bold mb-4">Meistara Panelis</h2>
                    <p class="text-gray-600 mb-6">Pārvaldiet savus piedāvājumus un meklējiet jaunus klientus.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border p-4 rounded hover:bg-gray-50 cursor-pointer"
                            @click="$router.push('/requests')">
                            <h3 class="font-bold text-lg text-blue-600">Meklēt Darbus</h3>
                            <p class="text-sm text-gray-500">Skatīt klientu pieprasījumus.</p>
                        </div>
                        <div class="border p-4 rounded hover:bg-gray-50 cursor-pointer"
                            @click="$router.push('/profile')">
                            <h3 class="font-bold text-lg text-green-600">Mans Profils</h3>
                            <p class="text-sm text-gray-500">Rediģēt pieredzi, portfolio un kontaktinformāciju.</p>
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
    // 1. Check if user is logged in
    const userData = localStorage.getItem('user');

    if (!userData) {
        // If no user found, kick them back to login
        router.push('/login');
    } else {
        // Load user data into the variable
        user.value = JSON.parse(userData);
    }
});

const logout = () => {
    // Clear storage and redirect
    localStorage.removeItem('user');
    router.push('/login');
};
</script>