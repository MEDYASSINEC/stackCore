import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { requireAuth } from '../../../middlewares/auth';
import { recyclingSchema } from '../../../services/validation';
import { handleApiError } from '../../../utils/errors';

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
