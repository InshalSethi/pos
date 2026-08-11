<template>
  <div class="relative w-full">
    <label v-if="label" class="block text-xs font-bold text-slate-700 mb-1.5">{{ label }}</label>

    <div class="relative flex items-center w-full border border-slate-200 rounded-md bg-white shadow-sm transition-all focus-within:ring-2 focus-within:ring-slate-200 focus-within:border-slate-300">
      <!-- Region Selector Dropdown Button -->
      <button
        type="button"
        @click="open = !open"
        class="flex items-center gap-1.5 px-3 py-2 text-sm border-r border-slate-200 bg-slate-50 rounded-l-md hover:bg-slate-100 transition-colors shrink-0 text-slate-700 outline-none select-none cursor-pointer"
      >
        <span class="font-bold text-slate-800">{{ selectedCountry.code }}</span>
        <span class="text-xs text-slate-500 font-medium">{{ selectedCountry.dialCode }}</span>
        <svg
          class="w-3.5 h-3.5 text-slate-400 ml-0.5 transition-transform duration-200"
          :class="{ 'rotate-180': open }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <!-- Phone Number Input -->
      <input
        type="text"
        :value="phoneNumber"
        @input="handlePhoneInput"
        :placeholder="selectedCountry.placeholder"
        class="w-full border-0 bg-transparent px-3 py-2 text-sm text-slate-800 placeholder-slate-400 outline-none focus:outline-none focus:ring-0"
      />

      <!-- Phone Icon -->
      <div class="pr-3 text-slate-400 flex items-center shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
        </svg>
      </div>

      <!-- Dropdown Popover -->
      <div
        v-if="open"
        @click.stop
        class="absolute top-full left-0 mt-1 w-72 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden py-2 focus:outline-none"
      >
        <!-- Search input -->
        <div class="px-2.5 pb-2 border-b border-slate-100" @click.stop>
          <div class="relative">
            <input
              type="text"
              v-model="searchQuery"
              @click.stop
              @focus.stop
              @keydown.stop
              placeholder="Search country or code..."
              class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-md text-xs text-slate-700 outline-none focus:border-slate-400 focus:bg-white transition-all"
            />
            <svg
              class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <!-- Country List -->
        <div class="max-h-52 overflow-y-auto py-1 custom-scrollbar">
          <button
            v-for="c in filteredCountries"
            :key="c.code + c.dialCode"
            type="button"
            @click="selectCountry(c)"
            class="w-full text-left px-3 py-2 text-xs flex items-center justify-between hover:bg-slate-50 transition-colors"
            :class="selectedCountry.code === c.code ? 'bg-slate-50 font-semibold text-indigo-600' : 'text-slate-700'"
          >
            <div class="flex items-center gap-2 truncate">
              <span class="text-base leading-none">{{ c.flag }}</span>
              <span class="truncate">{{ c.name }}</span>
              <span class="text-[10px] uppercase tracking-wider px-1 py-0.5 rounded bg-slate-100 text-slate-500 font-bold">{{ c.code }}</span>
            </div>
            <span class="font-mono text-slate-400 font-medium ml-2 text-xs">{{ c.dialCode }}</span>
          </button>
          <div v-if="filteredCountries.length === 0" class="px-3 py-4 text-center text-xs text-slate-400">
            No country found
          </div>
        </div>
      </div>
    </div>

    <!-- Dynamic Helper Text (Example) -->
    <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1.5">
      <span class="italic text-slate-400">Example:</span>
      <span class="font-mono text-indigo-600 font-semibold italic">{{ selectedCountry.dialCode }} {{ selectedCountry.examplePattern }}</span>
    </p>

    <p v-if="error" class="text-red-500 text-xs mt-1 font-medium block">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Company Phone' },
  error: { type: String, default: '' },
  defaultCountry: { type: String, default: 'PK' }
});

const emit = defineEmits(['update:modelValue', 'change']);

const countries = [
  { code: 'PK', name: 'Pakistan', dialCode: '+92', flag: '🇵🇰', placeholder: '300 1234567', examplePattern: '300 1234567', digits: 10 },
  { code: 'US', name: 'United States', dialCode: '+1', flag: '🇺🇸', placeholder: '201-555-0123', examplePattern: '201-555-0123', digits: 10 },
  { code: 'UK', name: 'United Kingdom', dialCode: '+44', flag: '🇬🇧', placeholder: '7911 123456', examplePattern: '7911 123456', digits: 10 },
  { code: 'AE', name: 'United Arab Emirates', dialCode: '+971', flag: '🇦🇪', placeholder: '50 1234567', examplePattern: '50 1234567', digits: 9 },
  { code: 'SA', name: 'Saudi Arabia', dialCode: '+966', flag: '🇸🇦', placeholder: '50 1234567', examplePattern: '50 1234567', digits: 9 },
  { code: 'CA', name: 'Canada', dialCode: '+1', flag: '🇨🇦', placeholder: '416-555-0123', examplePattern: '416-555-0123', digits: 10 },
  { code: 'IN', name: 'India', dialCode: '+91', flag: '🇮🇳', placeholder: '98765 43210', examplePattern: '98765 43210', digits: 10 },
  { code: 'DE', name: 'Germany', dialCode: '+49', flag: '🇩🇪', placeholder: '151 12345678', examplePattern: '151 12345678', digits: 11 },
  { code: 'FR', name: 'France', dialCode: '+33', flag: '🇫🇷', placeholder: '6 12 34 56 78', examplePattern: '6 12 34 56 78', digits: 9 },
  { code: 'AU', name: 'Australia', dialCode: '+61', flag: '🇦🇺', placeholder: '412 345 678', examplePattern: '412 345 678', digits: 9 },
  { code: 'QA', name: 'Qatar', dialCode: '+974', flag: '🇶🇦', placeholder: '3312 3456', examplePattern: '3312 3456', digits: 8 },
  { code: 'OM', name: 'Oman', dialCode: '+968', flag: '🇴🇲', placeholder: '9123 4567', examplePattern: '9123 4567', digits: 8 },
  { code: 'KW', name: 'Kuwait', dialCode: '+965', flag: '🇰🇼', placeholder: '9123 4567', examplePattern: '9123 4567', digits: 8 },
  { code: 'BH', name: 'Bahrain', dialCode: '+973', flag: '🇧🇭', placeholder: '3912 3456', examplePattern: '3912 3456', digits: 8 },
  { code: 'MY', name: 'Malaysia', dialCode: '+60', flag: '🇲🇾', placeholder: '12-345 6789', examplePattern: '12-345 6789', digits: 9 },
  { code: 'SG', name: 'Singapore', dialCode: '+65', flag: '🇸🇬', placeholder: '8123 4567', examplePattern: '8123 4567', digits: 8 },
  { code: 'TR', name: 'Turkey', dialCode: '+90', flag: '🇹🇷', placeholder: '532 123 45 67', examplePattern: '532 123 45 67', digits: 10 },
  { code: 'CN', name: 'China', dialCode: '+86', flag: '🇨🇳', placeholder: '138 1234 5678', examplePattern: '138 1234 5678', digits: 11 }
];

const open = ref(false);
const searchQuery = ref('');
const selectedCountry = ref(countries.find(c => c.code === props.defaultCountry) || countries[0]);
const phoneNumber = ref('');

const filteredCountries = computed(() => {
  if (!searchQuery.value.trim()) return countries;
  const q = searchQuery.value.toLowerCase();
  return countries.filter(c =>
    c.name.toLowerCase().includes(q) ||
    c.code.toLowerCase().includes(q) ||
    c.dialCode.includes(q)
  );
});

const closeDropdown = () => {
  open.value = false;
  searchQuery.value = '';
};

const selectCountry = (c) => {
  selectedCountry.value = c;
  closeDropdown();
  syncValue();
};

const parseAndSet = (val) => {
  if (!val) {
    phoneNumber.value = '';
    return;
  }
  let clean = String(val).trim();
  if (clean.startsWith('+')) {
    const sorted = [...countries].sort((a, b) => b.dialCode.length - a.dialCode.length);
    const matched = sorted.find(c => clean.startsWith(c.dialCode));
    if (matched) {
      selectedCountry.value = matched;
      clean = clean.slice(matched.dialCode.length);
    }
  }
  phoneNumber.value = clean.replace(/\D/g, '').slice(0, selectedCountry.value.digits);
  syncValue();
};

const handlePhoneInput = (event) => {
  let val = event.target.value;
  if (val.startsWith('+')) {
    let clean = val.trim();
    const sorted = [...countries].sort((a, b) => b.dialCode.length - a.dialCode.length);
    const matched = sorted.find(c => clean.startsWith(c.dialCode));
    if (matched) {
      selectedCountry.value = matched;
      clean = clean.slice(matched.dialCode.length);
    }
    phoneNumber.value = clean.replace(/\D/g, '').slice(0, selectedCountry.value.digits);
  } else {
    phoneNumber.value = val.replace(/\D/g, '').slice(0, selectedCountry.value.digits);
  }
  syncValue();
};

const syncValue = () => {
  const digits = phoneNumber.value;
  if (!digits) {
    emit('update:modelValue', '');
    emit('change', { e164: '', country: selectedCountry.value });
    return;
  }
  const fullPhoneNumber = `${selectedCountry.value.dialCode}${digits}`;
  emit('update:modelValue', fullPhoneNumber);
  emit('change', { e164: fullPhoneNumber, country: selectedCountry.value, rawDigits: digits });
};

watch(() => props.modelValue, (newVal) => {
  if (newVal !== (selectedCountry.value.dialCode + phoneNumber.value)) {
    parseAndSet(newVal);
  }
});

onMounted(() => {
  if (props.modelValue) {
    parseAndSet(props.modelValue);
  }
});
</script>
