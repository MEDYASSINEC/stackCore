import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { HttpError, requireAuth } from '../../../middlewares/auth';
import { apiError } from '../../../utils/errors';

export const GET: APIRoute = async (ctx) => {
  try {
    const user = requireAuth(ctx);
    const orders = await prisma.order.findMany({ where: user.role === 'CLIENT' ? { userId: user.userId } : {}, include: { items: true } });
    return new Response(JSON.stringify(orders));
  } catch (error) {
    if (error instanceof HttpError) return apiError(error.message, error.status);
    return apiError('Internal server error', 500);
  }
};
