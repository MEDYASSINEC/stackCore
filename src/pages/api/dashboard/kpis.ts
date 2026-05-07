import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { requireAuth } from '../../../middlewares/auth';
import { requireRole } from '../../../middlewares/rbac';
import { handleApiError } from '../../../utils/errors';

export const GET: APIRoute = async (ctx) => {
  try {
    const auth = requireAuth(ctx);
    requireRole(auth.role, ['ADMIN']);
    const [users, orders, products, recycling, revenue] = await Promise.all([
      prisma.user.count(),
      prisma.order.count(),
      prisma.product.count(),
      prisma.recyclingRequest.count(),
      prisma.order.aggregate({ _sum: { total: true } })
    ]);
    return new Response(JSON.stringify({ data: { users, orders, products, recycling, revenue: revenue._sum.total ?? 0 } }));
  } catch (error) {
    return handleApiError(error);
  }
};
