import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { requireAuth } from '../../../middlewares/auth';
import { handleApiError } from '../../../utils/errors';

export const GET: APIRoute = async (ctx) => {
  try {
    const auth = requireAuth(ctx);
    const user = await prisma.user.findUnique({ where: { id: auth.userId }, select: { email: true, loyaltyPoints: true, role: true } });
    return new Response(JSON.stringify({ data: user }));
  } catch (error) {
    return handleApiError(error);
  }
};
