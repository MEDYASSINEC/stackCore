import type { APIRoute } from 'astro';
import bcrypt from 'bcrypt';
import { prisma } from '../../../lib/prisma';
import { signAccessToken, signRefreshToken } from '../../../lib/jwt';
import { registerSchema } from '../../../services/validation';
import { apiError, handleApiError } from '../../../utils/errors';

export const POST: APIRoute = async ({ request }) => {
  try {
    const body = registerSchema.parse(await request.json());
    const exists = await prisma.user.findUnique({ where: { email: body.email } });
    if (exists) return apiError('Email already used', 409);
    const hash = await bcrypt.hash(body.password, 12);
    const user = await prisma.user.create({ data: { email: body.email, passwordHash: hash, role: body.role } });
    const payload = { userId: user.id, role: user.role as any, email: user.email };
    return new Response(JSON.stringify({ accessToken: signAccessToken(payload), refreshToken: signRefreshToken(payload), requiresEmailVerification: true }));
  } catch (error) {
    return handleApiError(error);
  }
};
