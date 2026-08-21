/**
 * Credit Card Input Formatting and Validation Utility
 */

export function formatCardNumber(value) {
  if (!value) return '';
  // Strip all non-digits, limit to 16 digits
  const digits = value.replace(/\D/g, '').slice(0, 16);
  // Group into 4-digit blocks
  const parts = digits.match(/.{1,4}/g);
  return parts ? parts.join(' ') : digits;
}

export function formatCardExpiry(value) {
  if (!value) return '';
  // Strip all non-digits, limit to 4 digits (MMYY)
  let digits = value.replace(/\D/g, '').slice(0, 4);
  if (digits.length >= 2) {
    return digits.slice(0, 2) + '/' + digits.slice(2);
  }
  return digits;
}

export function formatCardCvc(value) {
  if (!value) return '';
  // Strip all non-digits, limit to exactly 3 digits
  return value.replace(/\D/g, '').slice(0, 3);
}

export function validateCardNumber(cardNumber) {
  const digits = (cardNumber || '').replace(/\D/g, '');
  if (!digits || digits.length !== 16) {
    return { valid: false, message: 'Please enter a valid 16-digit card number.' };
  }
  return { valid: true, message: '' };
}

export function validateCardExpiry(cardExpiry) {
  if (!cardExpiry) {
    return { valid: false, message: 'Please enter a valid card expiry date in MM/YY format (future date required).' };
  }
  const clean = cardExpiry.replace(/\s/g, '');
  const match = clean.match(/^(0[1-9]|1[0-2])\/?([0-9]{2})$/);
  if (!match) {
    return { valid: false, message: 'Please enter a valid card expiry date in MM/YY format (future date required).' };
  }
  const month = parseInt(match[1], 10);
  const year = 2000 + parseInt(match[2], 10);
  
  const now = new Date();
  const currentYear = now.getFullYear();
  const currentMonth = now.getMonth() + 1; // 1-12
  
  if (year < currentYear || (year === currentYear && month < currentMonth)) {
    return { valid: false, message: 'Please enter a valid card expiry date in MM/YY format (future date required).' };
  }
  return { valid: true, message: '' };
}

export function validateCardCvc(cardCvc) {
  const digits = (cardCvc || '').replace(/\D/g, '');
  if (!digits || digits.length !== 3) {
    return { valid: false, message: 'CVV must be exactly 3 digits.' };
  }
  return { valid: true, message: '' };
}

export function useCardValidation() {
  return {
    formatCardNumber,
    formatCardExpiry,
    formatCardCvc,
    validateCardNumber,
    validateCardExpiry,
    validateCardCvc,
  };
}
