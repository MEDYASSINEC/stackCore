import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { requireAuth } from '../../../middlewares/auth';

export const POST: APIRoute = async (ctx) => {
  const user = requireAuth(ctx);
  const body = await ctx.request.json();
  const request = await prisma.recyclingRequest.create({ data: { userId: user.userId, description: body.description, photoUrl: body.photoUrl ?? '' } });
  return new Response(JSON.stringify(request), { status: 201 });
};
