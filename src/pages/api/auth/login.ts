import type { APIRoute } from 'astro';
import bcrypt from 'bcrypt';
import { prisma } from '../../../lib/prisma';
import { signAccessToken, signRefreshToken } from '../../../lib/jwt';
import { loginSchema } from '../../../services/validation';
import { handleApiError, apiError } from '../../../utils/errors';

export const POST: APIRoute = async ({ request }) => {
  try {
    const body = loginSchema.parse(await request.json());
    const user = await prisma.user.findUnique({ where: { email: body.email } });
    if (!user || !(await bcrypt.compare(body.password, user.passwordHash))) return apiError('Invalid credentials', 401);
    const payload = { userId: user.id, role: user.role as any, email: user.email };
    return new Response(JSON.stringify({ accessToken: signAccessToken(payload), refreshToken: signRefreshToken(payload) }));
  } catch (error) {
    return handleApiError(error);
  }
};
