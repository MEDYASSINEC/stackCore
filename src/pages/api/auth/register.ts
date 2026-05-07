import type { APIRoute } from 'astro';
import bcrypt from 'bcrypt';
import { prisma } from '../../../lib/prisma';
import { signAccessToken } from '../../../lib/jwt';
import { registerSchema } from '../../../services/validation';
import { apiError } from '../../../utils/errors';
export const POST: APIRoute = async ({ request }) => {
  try {
    const body = registerSchema.parse(await request.json());
    const hash = await bcrypt.hash(body.password, 12);
    const user = await prisma.user.create({ data: { email: body.email, passwordHash: hash, role: 'CLIENT' } });
    return new Response(JSON.stringify({ accessToken: signAccessToken({ userId: user.id, role: user.role as any, email: user.email }) }));
  } catch {
    return apiError('Invalid register payload');
  }
};
