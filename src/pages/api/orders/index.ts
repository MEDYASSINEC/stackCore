import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { requireAuth } from '../../../middlewares/auth';
import { requireRole } from '../../../middlewares/rbac';
import { checkoutSchema } from '../../../services/validation';
import { handleApiError, apiError } from '../../../utils/errors';

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

    const result = await prisma.$transaction(async (tx) => {
      const products = await tx.product.findMany({ where: { id: { in: body.items.map((i) => i.productId) } } });
      for (const item of body.items) {
        const product = products.find((p) => p.id === item.productId);
        if (!product || product.stock < item.quantity) return apiError(`Stock insuffisant pour ${product?.name ?? item.productId}`, 400);
      }

      const total = body.items.reduce((sum, item) => {
        const p = products.find((x) => x.id === item.productId);
        return sum + (p?.price ?? 0) * item.quantity;
      }, 0);

      const order = await tx.order.create({
        data: {
          userId: auth.userId,
          total,
          status: body.paymentMethod === 'SIMULATED' ? 'PAID' : 'PENDING',
          items: { create: body.items.map((i) => ({ productId: i.productId, quantity: i.quantity, unitPrice: products.find((p) => p.id === i.productId)?.price ?? 0 })) }
        },
        include: { items: true }
      });

      if (order.status === 'PAID') {
        await Promise.all(body.items.map((i) => tx.product.update({ where: { id: i.productId }, data: { stock: { decrement: i.quantity } } })));
        await tx.user.update({ where: { id: auth.userId }, data: { loyaltyPoints: { increment: Math.floor(total / 10) } } });
      }

      return order;
    });

    if (result instanceof Response) return result;
    return new Response(JSON.stringify({ data: result, payment: { provider: body.paymentMethod, status: result.status } }), { status: 201 });
  } catch (error) {
    return handleApiError(error);
  }
};
