import type { APIRoute } from 'astro';
import { z } from 'zod';
import { prisma } from '../../../lib/prisma';
import { requireAuth } from '../../../middlewares/auth';
import { recyclingSchema } from '../../../services/validation';
import { handleApiError } from '../../../utils/errors';

const statusUpdateSchema = z.object({ id: z.string(), status: z.enum(['SUBMITTED', 'REVIEWED', 'APPROVED', 'REWARDED', 'REJECTED']), rewardPoints: z.number().int().nonnegative().default(0) });

export const GET: APIRoute = async (ctx) => {
  try {
    const auth = requireAuth(ctx);
    const data = await prisma.recyclingRequest.findMany({ where: auth.role === 'ADMIN' ? {} : { userId: auth.userId }, orderBy: { createdAt: 'desc' } });
    return new Response(JSON.stringify({ data }));
  } catch (error) {
    return handleApiError(error);
  }
};

export const POST: APIRoute = async (ctx) => {
  try {
    const auth = requireAuth(ctx);
    const body = recyclingSchema.parse(await ctx.request.json());
    const data = await prisma.recyclingRequest.create({ data: { userId: auth.userId, ...body } });
    return new Response(JSON.stringify({ data }), { status: 201 });
  } catch (error) {
    return handleApiError(error);
  }
};

export const PATCH: APIRoute = async (ctx) => {
  try {
    const auth = requireAuth(ctx);
    if (auth.role !== 'ADMIN') return new Response(JSON.stringify({ error: 'Forbidden' }), { status: 403 });
    const body = statusUpdateSchema.parse(await ctx.request.json());
    const updated = await prisma.$transaction(async (tx) => {
      const req = await tx.recyclingRequest.update({ where: { id: body.id }, data: { status: body.status, rewardPoints: body.rewardPoints } });
      if (body.status === 'REWARDED' && body.rewardPoints > 0) {
        await tx.user.update({ where: { id: req.userId }, data: { loyaltyPoints: { increment: body.rewardPoints } } });
      }
      return req;
    });
    return new Response(JSON.stringify({ data: updated }));
  } catch (error) {
    return handleApiError(error);
  }
};
