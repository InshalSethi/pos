/**
 * Credit Card Input Formatting and Validation Utility
 */

export function formatCardNumber(value) {
  if (!value) return '';
  // Strip all non-digits
  const digits = value.replace(/\D/g, '').slice(0, 19);
  // Group into 4-digit blocks
  const parts = digits.match(/.{1,4}/g);
  return parts ? parts.join(' ') : digits;
}

export function formatCardExpiry(value) {
  if (!value) return '';
  // Strip all non-digits
  let digits = value.replace(/\D/g, '').slice(0, 4);
  if (digits.length >= 3) {
    return digits.slice(0, 2) + '/' + digits.slice(2);
  }
  return digits;
}

export function formatCardCvc(value) {
  if (!value) return '';
  return value.replace(/\D/g, '').slice(0, 4);
}

export function validateLuhn(numberStr) {
  const digits = (numberStr || '').replace(/\D/g, '');
  if (digits.length < 13 || digits.length > 19) return false;
  
  let sum = 0;
  let shouldDouble = false;
  for (let i = digits.length - 1; i >= 0; i--) {
    let digit = parseInt(digits.charAt(i), 10);
    if (shouldDouble) {
      digit *= 2;
      if (digit > 9) digit -= 9;
    }
    sum += digit;
    shouldDouble = !shouldDouble;
  }
  return sum % 10 === 0;
}

export function validateCardNumber(cardNumber) {
  const digits = (cardNumber || '').replace(/\D/g, '');
  if (!digits) {
    return { valid: false, message: 'Card number is required.' };
  }
  if (digits.length < 13 || digits.length > 19) {
    return { valid: false, message: 'Card number must be between 13 and 19 digits.' };
  }
  if (!validateLuhn(digits)) {
    return { valid: false, message: 'Invalid card number format (Luhn check failed).' };
  }
  return { valid: true, message: '' };
}

export function validateCardExpiry(cardExpiry) {
  if (!cardExpiry) {
    return { valid: false, message: 'Expiry date is required.' };
  }
  const clean = cardExpiry.replace(/\s/g, '');
  const match = clean.match(/^(0[1-9]|1[0-2])\/?([0-9]{2})$/);
  if (!match) {
    return { valid: false, message: 'Expiry must be in MM/YY format.' };
  }
  const month = parseInt(match[1], 10);
  const year = 2000 + parseInt(match[2], 10);
  
  const now = new Date();
  const currentYear = now.getFullYear();
  const currentMonth = now.getMonth() + 1; // 1-12
  
  if (year < currentYear || (year === currentYear && month < currentMonth)) {
    return { valid: false, message: 'Card has expired.' };
  }
  return { valid: true, message: '' };
}

export function validateCardCvc(cardCvc) {
  const digits = (cardCvc || '').replace(/\D/g, '');
  if (!digits) {
    return { valid: false, message: 'CVC is required.' };
  }
  if (digits.length < 3 || digits.length > 4) {
    return { valid: false, message: 'CVC must be 3 or 4 digits.' };
  }
  return { valid: true, message: '' };
}

export function useCardValidation() {
  return {
    formatCardNumber,
    formatCardExpiry,
    formatCardCvc,
    validateLuhn,
    validateCardNumber,
    validateCardExpiry,
    validateCardCvc,
  };
}
