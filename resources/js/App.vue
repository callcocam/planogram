<template>
    <div class="px-10">
        <Header :planogram="record" />
        <div  >
             <Gondolas :planogram="record" />
        </div>
    </div>
</template>
<script setup lang="ts">
import Gondolas from './Gondolas.vue';
import Header from './Header.vue';
const props = defineProps({
    record: {
        type: Object,
        required: true,
    },
});

console.log(props.record);
</script>
