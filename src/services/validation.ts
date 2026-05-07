import { z } from 'zod';

export const registerSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8).regex(/[A-Z]/).regex(/[0-9]/),
  role: z.enum(['ADMIN', 'CLIENT', 'SUPPLIER']).default('CLIENT')
});

export const loginSchema = z.object({ email: z.string().email(), password: z.string().min(8) });
export const refreshSchema = z.object({ refreshToken: z.string().min(10) });

export const productSchema = z.object({
  name: z.string().min(2),
  description: z.string().min(5),
  price: z.number().positive(),
  stock: z.number().int().nonnegative(),
  category: z.string().optional(),
  imageUrl: z.string().url().optional()
});

export const checkoutSchema = z.object({
  items: z.array(z.object({ productId: z.string().min(5), quantity: z.number().int().positive() })).min(1),
  paymentMethod: z.enum(['STRIPE', 'SIMULATED']).default('SIMULATED')
});

export const recyclingSchema = z.object({
  description: z.string().min(10),
  photoUrl: z.string().url(),
});
