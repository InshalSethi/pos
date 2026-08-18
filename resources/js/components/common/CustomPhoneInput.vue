<template>
  <div class="w-full">
    <!-- Optional Label -->
    <label v-if="label" class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1.5">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Outer Input Container -->
    <div
      ref="containerRef"
      class="relative flex items-center w-full border rounded-lg bg-white dark:bg-zinc-950 transition-all shadow-xs"
      :class="[
        (localError || error) ? 'border-red-300 dark:border-red-700 focus-within:ring-2 focus-within:ring-red-500/10' : 'border-slate-200 dark:border-zinc-700 focus-within:ring-2 focus-within:ring-indigo-500/10 focus-within:border-indigo-500',
        disabled ? 'opacity-60 cursor-not-allowed bg-slate-50 dark:bg-zinc-900/50' : ''
      ]"
    >
      <!-- Country Code Selector Button (Left Side) -->
      <div class="relative shrink-0 border-r border-slate-200 dark:border-zinc-700">
        <button
          type="button"
          @click="!disabled && toggleDropdown()"
          :disabled="disabled"
          class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-slate-50/80 dark:bg-zinc-900/80 hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-800 dark:text-zinc-200 rounded-l-lg transition-colors cursor-pointer select-none h-full min-h-[36px]"
        >
          <span class="font-bold text-slate-900 dark:text-zinc-100 tracking-wide">{{ selectedCountry.code }}</span>
          <span class="text-slate-500 dark:text-zinc-400 font-normal">{{ selectedCountry.dialCode }}</span>
          <svg
            class="w-3 h-3 text-slate-400 transition-transform duration-200 shrink-0"
            :class="{ 'rotate-180': isOpen }"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Floating Country Dropdown Menu -->
        <div
          v-if="isOpen"
          class="absolute left-0 top-full mt-1.5 z-[9999] w-64 max-h-64 overflow-hidden rounded-xl shadow-xl bg-white dark:bg-[#1E1E2D] border border-slate-200 dark:border-zinc-800 py-1 flex flex-col animate-in fade-in zoom-in-95 duration-100 text-left"
        >
          <!-- Search Box -->
          <div class="p-2 border-b border-slate-100 dark:border-zinc-800 shrink-0">
            <input
              ref="searchInputRef"
              v-model="searchQuery"
              type="text"
              placeholder="Search country or code..."
              class="w-full px-2.5 py-1.5 text-xs border border-slate-200 dark:border-zinc-700 rounded-lg bg-slate-50 dark:bg-zinc-900 text-slate-800 dark:text-zinc-200 outline-none focus:border-indigo-500 transition-all placeholder-slate-400 dark:placeholder-zinc-500"
            />
          </div>

          <!-- Country List -->
          <div class="overflow-y-auto max-h-48 custom-scrollbar divide-y divide-slate-50 dark:divide-zinc-800/40">
            <button
              v-for="c in filteredCountries"
              :key="c.code + c.dialCode"
              type="button"
              @click="selectCountry(c)"
              class="w-full flex items-center justify-between px-3 py-2 text-xs text-left transition-colors hover:bg-slate-50 dark:hover:bg-zinc-800/80 cursor-pointer"
              :class="selectedCountry.code === c.code ? 'bg-indigo-50/70 dark:bg-indigo-950/40 font-semibold text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-zinc-300'"
            >
              <div class="flex items-center gap-2 truncate">
                <span class="text-sm shrink-0">{{ c.flag }}</span>
                <span class="truncate">{{ c.name }}</span>
              </div>
              <span class="text-slate-400 dark:text-zinc-500 font-mono text-[11px] shrink-0 ml-2 font-medium">{{ c.dialCode }}</span>
            </button>
            <div v-if="filteredCountries.length === 0" class="px-3 py-4 text-center text-xs text-slate-400 dark:text-zinc-500">
              No matching countries
            </div>
          </div>
        </div>
      </div>

      <!-- Phone Number Input (Right Side) -->
      <div class="relative flex-1 flex items-center">
        <input
          v-model="displayValue"
          type="tel"
          :placeholder="placeholder || selectedCountry.placeholder"
          :disabled="disabled"
          :maxlength="selectedCountry.mask.length"
          @keydown="handleKeyDown"
          @input="handleInput"
          @blur="handleBlur"
          class="w-full px-3 py-2 text-xs bg-transparent text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-500 outline-none pr-8 font-medium font-mono"
        />

        <!-- Phone Icon on Far Right -->
        <div class="absolute right-2.5 text-slate-400 dark:text-zinc-500 pointer-events-none shrink-0">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <p v-if="localError || error" class="mt-1 text-[10px] text-red-500 font-medium">
      {{ localError || (Array.isArray(error) ? error[0] : error) }}
    </p>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: [String, Array],
    default: null
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  defaultCountryCode: {
    type: String,
    default: 'PK'
  }
});

const emit = defineEmits(['update:modelValue']);

const containerRef = ref(null);
const searchInputRef = ref(null);

const countries = [
  { code: 'PK', name: 'Pakistan', dialCode: '+92', flag: '🇵🇰', mask: '3## #######', placeholder: '300 1234567', digitsCount: 10, stripLeadingZero: true },
  { code: 'US', name: 'United States', dialCode: '+1', flag: '🇺🇸', mask: '(###) ###-####', placeholder: '(555) 000-0000', digitsCount: 10, stripLeadingZero: false },
  { code: 'CA', name: 'Canada', dialCode: '+1', flag: '🇨🇦', mask: '(###) ###-####', placeholder: '(555) 000-0000', digitsCount: 10, stripLeadingZero: false },
  { code: 'GB', name: 'United Kingdom', dialCode: '+44', flag: '🇬🇧', mask: '7### ######', placeholder: '7911 123456', digitsCount: 10, stripLeadingZero: true },
  { code: 'AE', name: 'United Arab Emirates', dialCode: '+971', flag: '🇦🇪', mask: '5# #######', placeholder: '50 1234567', digitsCount: 9, stripLeadingZero: true },
  { code: 'SA', name: 'Saudi Arabia', dialCode: '+966', flag: '🇸🇦', mask: '5# #######', placeholder: '50 1234567', digitsCount: 9, stripLeadingZero: true },
  { code: 'IN', name: 'India', dialCode: '+91', flag: '🇮🇳', mask: '##### #####', placeholder: '98765 43210', digitsCount: 10, stripLeadingZero: true },
  { code: 'AU', name: 'Australia', dialCode: '+61', flag: '🇦🇺', mask: '4## ### ###', placeholder: '412 345 678', digitsCount: 9, stripLeadingZero: true },
  { code: 'DE', name: 'Germany', dialCode: '+49', flag: '🇩🇪', mask: '1## ########', placeholder: '151 23456789', digitsCount: 11, stripLeadingZero: true },
  { code: 'FR', name: 'France', dialCode: '+33', flag: '🇫🇷', mask: '6 ## ## ## ##', placeholder: '6 12 34 56 78', digitsCount: 9, stripLeadingZero: true },
  { code: 'CN', name: 'China', dialCode: '+86', flag: '🇨🇳', mask: '1## #### ####', placeholder: '138 1234 5678', digitsCount: 11, stripLeadingZero: false },
  { code: 'JP', name: 'Japan', dialCode: '+81', flag: '🇯🇵', mask: '90 #### ####', placeholder: '90 1234 5678', digitsCount: 10, stripLeadingZero: true },
  { code: 'SG', name: 'Singapore', dialCode: '+65', flag: '🇸🇬', mask: '#### ####', placeholder: '9123 4567', digitsCount: 8, stripLeadingZero: false },
  { code: 'MY', name: 'Malaysia', dialCode: '+60', flag: '🇲🇾', mask: '1# #### ####', placeholder: '12 345 6789', digitsCount: 9, stripLeadingZero: true },
  { code: 'OM', name: 'Oman', dialCode: '+968', flag: '🇴🇲', mask: '#### ####', placeholder: '9123 4567', digitsCount: 8, stripLeadingZero: false },
  { code: 'QA', name: 'Qatar', dialCode: '+974', flag: '🇶🇦', mask: '#### ####', placeholder: '3312 3456', digitsCount: 8, stripLeadingZero: false },
  { code: 'KW', name: 'Kuwait', dialCode: '+965', flag: '🇰🇼', mask: '#### ####', placeholder: '9123 4567', digitsCount: 8, stripLeadingZero: false },
  { code: 'BH', name: 'Bahrain', dialCode: '+973', flag: '🇧🇭', mask: '#### ####', placeholder: '3912 3456', digitsCount: 8, stripLeadingZero: false },
  { code: 'BD', name: 'Bangladesh', dialCode: '+880', flag: '🇧🇩', mask: '1### ######', placeholder: '1712 345678', digitsCount: 10, stripLeadingZero: true },
  { code: 'LK', name: 'Sri Lanka', dialCode: '+94', flag: '🇱🇰', mask: '7# ### ####', placeholder: '77 123 4567', digitsCount: 9, stripLeadingZero: true },
  { code: 'PH', name: 'Philippines', dialCode: '+63', flag: '🇵🇭', mask: '9## ### ####', placeholder: '917 123 4567', digitsCount: 10, stripLeadingZero: true },
  { code: 'TR', name: 'Turkey', dialCode: '+90', flag: '🇹🇷', mask: '5## ### ####', placeholder: '501 234 5678', digitsCount: 10, stripLeadingZero: true },
  { code: 'EG', name: 'Egypt', dialCode: '+20', flag: '🇪🇬', mask: '1## ### ####', placeholder: '100 123 4567', digitsCount: 10, stripLeadingZero: true },
  { code: 'ZA', name: 'South Africa', dialCode: '+27', flag: '🇿🇦', mask: '## ### ####', placeholder: '82 123 4567', digitsCount: 9, stripLeadingZero: true },
  { code: 'IT', name: 'Italy', dialCode: '+39', flag: '🇮🇹', mask: '3## ### ####', placeholder: '312 345 6789', digitsCount: 10, stripLeadingZero: false },
  { code: 'ES', name: 'Spain', dialCode: '+34', flag: '🇪🇸', mask: '### ## ## ##', placeholder: '612 34 56 78', digitsCount: 9, stripLeadingZero: false },
  { code: 'NL', name: 'Netherlands', dialCode: '+31', flag: '🇳🇱', mask: '6 ########', placeholder: '6 12345678', digitsCount: 9, stripLeadingZero: true },
  { code: 'BR', name: 'Brazil', dialCode: '+55', flag: '🇧🇷', mask: '(##) 9####-####', placeholder: '(11) 91234-5678', digitsCount: 11, stripLeadingZero: true },
  { code: 'MX', name: 'Mexico', dialCode: '+52', flag: '🇲🇽', mask: '## #### ####', placeholder: '55 1234 5678', digitsCount: 10, stripLeadingZero: false },
  { code: 'RU', name: 'Russia', dialCode: '+7', flag: '🇷🇺', mask: '(###) ###-##-##', placeholder: '(912) 345-67-89', digitsCount: 10, stripLeadingZero: false }
];

const selectedCountry = ref(countries.find(c => c.code === props.defaultCountryCode) || countries[0]);
const rawDigits = ref('');
const displayValue = ref('');
const isOpen = ref(false);
const searchQuery = ref('');
const localError = ref('');
const isTouched = ref(false);

const filteredCountries = computed(() => {
  if (!searchQuery.value) return countries;
  const q = searchQuery.value.toLowerCase().trim();
  return countries.filter(c => 
    c.name.toLowerCase().includes(q) || 
    c.code.toLowerCase().includes(q) || 
    c.dialCode.includes(q)
  );
});

// Format raw digits according to a country's mask spec
const formatByMask = (digitsStr, spec) => {
  if (!digitsStr || !spec) return '';
  
  let digits = digitsStr;
  if (spec.stripLeadingZero && digits.startsWith('0')) {
    digits = digits.slice(1);
  }
  if (spec.digitsCount && digits.length > spec.digitsCount) {
    digits = digits.slice(0, spec.digitsCount);
  }

  const mask = spec.mask;
  let formatted = '';
  let digitIdx = 0;

  for (let i = 0; i < mask.length; i++) {
    if (digitIdx >= digits.length) break;

    const maskChar = mask[i];

    if (maskChar === '#') {
      formatted += digits[digitIdx];
      digitIdx++;
    } else if (/\d/.test(maskChar)) {
      if (digits[digitIdx] === maskChar) {
        formatted += maskChar;
        digitIdx++;
      } else {
        formatted += maskChar;
      }
    } else {
      formatted += maskChar;
    }
  }

  return formatted;
};

// Validate exact digit requirement
const validateInput = () => {
  const count = rawDigits.value.length;
  const requiredCount = selectedCountry.value.digitsCount;

  if (props.required && count === 0) {
    localError.value = `${props.label || 'Phone number'} is required`;
  } else if (count > 0 && count < requiredCount) {
    localError.value = `Must be exactly ${requiredCount} digits for ${selectedCountry.value.name} (${selectedCountry.value.dialCode})`;
  } else {
    localError.value = '';
  }
};

const formatOutput = () => {
  if (!displayValue.value.trim()) return '';
  return `${selectedCountry.value.dialCode} ${displayValue.value.trim()}`;
};

const sanitizeInputDigits = (val) => {
  let digits = val.replace(/\D/g, '');
  
  // If user pasted/entered the dial code at the beginning, strip it
  const dialDigits = selectedCountry.value.dialCode.replace(/\D/g, '');
  if (dialDigits && digits.startsWith(dialDigits)) {
    digits = digits.slice(dialDigits.length);
  }

  // Strip leading zero if specified
  if (selectedCountry.value.stripLeadingZero && digits.startsWith('0')) {
    digits = digits.slice(1);
  }

  // Limit to max digits
  if (digits.length > selectedCountry.value.digitsCount) {
    digits = digits.slice(0, selectedCountry.value.digitsCount);
  }

  return digits;
};

const handleKeyDown = (e) => {
  // Allow control & navigation keys
  const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
  if (allowedKeys.includes(e.key) || e.ctrlKey || e.metaKey || e.altKey) {
    return;
  }
  // Reject any character that is not a digit (0-9)
  if (!/^[0-9]$/.test(e.key)) {
    e.preventDefault();
  }
};

const handleInput = (e) => {
  const cleanDigits = sanitizeInputDigits(e.target.value);
  rawDigits.value = cleanDigits;
  displayValue.value = formatByMask(cleanDigits, selectedCountry.value);

  if (isTouched.value || cleanDigits.length === selectedCountry.value.digitsCount) {
    validateInput();
  } else if (localError.value && cleanDigits.length === 0 && !props.required) {
    localError.value = '';
  }

  emit('update:modelValue', formatOutput());
};

const handleBlur = () => {
  isTouched.value = true;
  validateInput();
};

const selectCountry = (c) => {
  selectedCountry.value = c;
  isOpen.value = false;
  searchQuery.value = '';

  // Re-mask raw digits for the new country format
  let cleanDigits = rawDigits.value;
  if (c.stripLeadingZero && cleanDigits.startsWith('0')) {
    cleanDigits = cleanDigits.slice(1);
  }
  if (cleanDigits.length > c.digitsCount) {
    cleanDigits = cleanDigits.slice(0, c.digitsCount);
  }

  rawDigits.value = cleanDigits;
  displayValue.value = formatByMask(cleanDigits, c);

  validateInput();
  emit('update:modelValue', formatOutput());
};

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
    nextTick(() => {
      if (searchInputRef.value) searchInputRef.value.focus();
    });
  }
};

const parseValue = (val) => {
  if (!val) {
    rawDigits.value = '';
    displayValue.value = '';
    localError.value = '';
    return;
  }
  const clean = val.trim();
  
  // Find matching country by dial code prefix
  const matched = countries.find(c => clean.startsWith(c.dialCode));
  if (matched) {
    selectedCountry.value = matched;
    const numPart = clean.slice(matched.dialCode.length).trim();
    let digits = numPart.replace(/\D/g, '');
    if (matched.stripLeadingZero && digits.startsWith('0')) {
      digits = digits.slice(1);
    }
    if (digits.length > matched.digitsCount) {
      digits = digits.slice(0, matched.digitsCount);
    }
    rawDigits.value = digits;
    displayValue.value = formatByMask(digits, matched);
  } else {
    let digits = clean.replace(/\D/g, '');
    if (selectedCountry.value.stripLeadingZero && digits.startsWith('0')) {
      digits = digits.slice(1);
    }
    if (digits.length > selectedCountry.value.digitsCount) {
      digits = digits.slice(0, selectedCountry.value.digitsCount);
    }
    rawDigits.value = digits;
    displayValue.value = formatByMask(digits, selectedCountry.value);
  }

  if (isTouched.value) {
    validateInput();
  }
};

const handleClickOutside = (e) => {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    isOpen.value = false;
  }
};

watch(() => props.modelValue, (newVal) => {
  const currentFormatted = formatOutput();
  if (newVal !== currentFormatted) {
    parseValue(newVal);
  }
}, { immediate: true });

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

