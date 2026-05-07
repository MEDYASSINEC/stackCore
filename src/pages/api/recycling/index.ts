import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { HttpError, requireAuth } from '../../../middlewares/auth';
import { apiError } from '../../../utils/errors';

export const POST: APIRoute = async (ctx) => {
  try {
    const user = requireAuth(ctx);
    const body = await ctx.request.json();
    const request = await prisma.recyclingRequest.create({ data: { userId: user.userId, description: body.description, photoUrl: body.photoUrl ?? '' } });
    return new Response(JSON.stringify(request), { status: 201 });
  } catch (error) {
    if (error instanceof HttpError) return apiError(error.message, error.status);
    return apiError('Internal server error', 500);
  }
};
