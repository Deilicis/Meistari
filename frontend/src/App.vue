<script setup>
import { RouterView, useRoute } from 'vue-router';
import AppHeader from '@/components/AppHeader.vue'; // Imports

const route = useRoute();
</script>

<template>
  <AppHeader v-if="!route.meta.hideHeader" />
  
  <RouterView />
</template>