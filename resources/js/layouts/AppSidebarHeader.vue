<script setup lang="ts">
// @ts-ignore
import Breadcrumbs from '@/components/Breadcrumbs.vue';
// @ts-ignore
import { SidebarTrigger } from '@/components/ui/sidebar';
import AppearanceTabs from './AppearanceTabs.vue';
// @ts-ignore
import type { BreadcrumbItemType } from '@/types';

defineProps<{
    breadcrumbs: BreadcrumbItemType[];
}>();
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex w-full items-center justify-between">
            <div class="flex items-center gap-2">
                <SidebarTrigger class="-ml-1" />
                <template v-if="breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>
            </div>
            <AppearanceTabs />
        </div>
    </header>
</template>
