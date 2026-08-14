<template>
  <CustomPhoneInput
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    :label="label"
    :error="error"
    :defaultCountryCode="defaultCountry"
  />
</template>

<script setup>
import CustomPhoneInput from '@/components/common/CustomPhoneInput.vue';

defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Company Phone' },
  error: { type: String, default: '' },
  defaultCountry: { type: String, default: 'PK' }
});

defineEmits(['update:modelValue', 'change']);
</script>
