import { z } from 'zod';

// Mengambil pesan error pertama untuk field spesifik dari Zod safeParse result
export const getFieldError = (result, field) => {
  if (result.success || !result.error) return null;
  const flat = z.flattenError(result.error);
  return flat.fieldErrors[field]?.[0] || null;
};
