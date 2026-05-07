import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { requireAuth } from '../../../middlewares/auth';

export const GET: APIRoute = async (ctx) => {
  const user = requireAuth(ctx);
  const orders = await prisma.order.findMany({ where: user.role === 'CLIENT' ? { userId: user.userId } : {}, include: { items: true } });
  return new Response(JSON.stringify(orders));
};
