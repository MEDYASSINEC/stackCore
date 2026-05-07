import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { requireAuth } from '../../../middlewares/auth';
import { requireRole } from '../../../middlewares/rbac';

export const GET: APIRoute = async (ctx) => {
  const user = requireAuth(ctx);
  requireRole(user.role, ['ADMIN']);
  const [users, orders, products] = await Promise.all([prisma.user.count(), prisma.order.count(), prisma.product.count()]);
  return new Response(JSON.stringify({ users, orders, products }));
};
