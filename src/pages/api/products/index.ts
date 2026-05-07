import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { productSchema } from '../../../services/validation';
import { apiError } from '../../../utils/errors';
import { HttpError, requireAuth } from '../../../middlewares/auth';
import { requireRole } from '../../../middlewares/rbac';

export const GET: APIRoute = async () => {
  const products = await prisma.product.findMany({ take: 20, orderBy: { createdAt: 'desc' } });
  return new Response(JSON.stringify(products));
};

export const POST: APIRoute = async (ctx) => {
  try {
    const user = requireAuth(ctx);
    requireRole(user.role, ['ADMIN', 'SUPPLIER']);
    const body = productSchema.parse(await ctx.request.json());
    const product = await prisma.product.create({ data: body });
    return new Response(JSON.stringify(product), { status: 201 });
  } catch (error) {
    if (error instanceof HttpError) return apiError(error.message, error.status);
    return apiError('Unauthorized or invalid product payload', 400);
  }
};
