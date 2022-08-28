<template>
    <select class="border-gray-300 rounded-md shadow-sm" v-model="modelValue"
            @input="$emit('update:modelValue', $event.target.value)">
        <option value="">Select an option</option>
        <option v-for="(item, key) in keys" :value="key">
            {{ item }}
        </option>
    </select>
</template>

<script>
export default {
    emits: ['update:modelValue'],

    props: ['modelValue', 'keys'],
}
</script>
