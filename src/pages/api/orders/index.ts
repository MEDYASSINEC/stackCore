import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { requireAuth } from '../../../middlewares/auth';
import { requireRole } from '../../../middlewares/rbac';
import { checkoutSchema } from '../../../services/validation';
import { handleApiError } from '../../../utils/errors';

export const GET: APIRoute = async (ctx) => {
  try {
    const auth = requireAuth(ctx);
    const where = auth.role === 'ADMIN' ? {} : { userId: auth.userId };
    const orders = await prisma.order.findMany({ where, include: { items: true }, orderBy: { createdAt: 'desc' } });
    return new Response(JSON.stringify({ data: orders }));
  } catch (error) {
    return handleApiError(error);
  }
};

export const POST: APIRoute = async (ctx) => {
  try {
    const auth = requireAuth(ctx);
    requireRole(auth.role, ['CLIENT', 'ADMIN']);
    const body = checkoutSchema.parse(await ctx.request.json());
    const products = await prisma.product.findMany({ where: { id: { in: body.items.map(i => i.productId) } } });
    const total = body.items.reduce((sum, item) => {
      const p = products.find(x => x.id === item.productId);
      return sum + (p?.price ?? 0) * item.quantity;
    }, 0);
    const order = await prisma.order.create({
      data: {
        userId: auth.userId,
        total,
        status: body.paymentMethod === 'SIMULATED' ? 'PAID' : 'PENDING',
        items: { create: body.items.map(i => ({ productId: i.productId, quantity: i.quantity, unitPrice: products.find(p => p.id === i.productId)?.price ?? 0 })) }
      },
      include: { items: true }
    });
    return new Response(JSON.stringify({ data: order, payment: { provider: body.paymentMethod, status: order.status } }), { status: 201 });
  } catch (error) {
    return handleApiError(error);
  }
};
