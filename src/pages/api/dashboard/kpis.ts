import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { HttpError, requireAuth } from '../../../middlewares/auth';
import { requireRole } from '../../../middlewares/rbac';
import { apiError } from '../../../utils/errors';

export const GET: APIRoute = async (ctx) => {
  try {
    const user = requireAuth(ctx);
    requireRole(user.role, ['ADMIN']);
    const [users, orders, products] = await Promise.all([prisma.user.count(), prisma.order.count(), prisma.product.count()]);
    return new Response(JSON.stringify({ users, orders, products }));
  } catch (error) {
    if (error instanceof HttpError) return apiError(error.message, error.status);
    return apiError('Internal server error', 500);
  }
};
