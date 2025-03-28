<template>
    <div class="px-10">
        <Header :planogram="record" />
    </div>
</template>
<script setup lang="ts">
import Header from './Header.vue';


const props = defineProps({
    record: {
        type: Object,
        required: true
    },
})

console.log(props.record)

</script>