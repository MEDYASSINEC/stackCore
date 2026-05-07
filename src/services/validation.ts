import { z } from 'zod';
export const registerSchema = z.object({ email: z.string().email(), password: z.string().min(8), role: z.enum(['ADMIN','CLIENT','SUPPLIER']).default('CLIENT') });
export const productSchema = z.object({ name: z.string().min(2), description: z.string().min(5), price: z.number().positive(), stock: z.number().int().nonnegative() });
