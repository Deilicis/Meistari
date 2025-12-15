<script setup>
import { RouterView, useRoute } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import NotificationToast from '@/components/NotificationToast.vue'

const route = useRoute()
</script>

<template>
  <NotificationToast /> <AppHeader v-if="!route.meta.hideHeader" />
  
  <RouterView />
</template>