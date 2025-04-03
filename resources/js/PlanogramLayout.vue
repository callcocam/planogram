<script setup lang="ts">
import AppContent from './layouts/AppContent.vue';
import AppShell from './layouts/AppShell.vue';
import AppSidebarHeader from './layouts/AppSidebarHeader.vue';
//@ts-ignore
import { Toaster } from '@/components/ui/toast';
//@ts-ignore
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell>
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <!-- Toast System -->
        <Toaster />
        <FlashMessageHandler />
    </AppShell>
</template>
