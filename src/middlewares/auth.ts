import type { APIContext } from 'astro';
import { verifyAccessToken } from '../lib/jwt';
import { apiError } from '../utils/errors';

export const requireAuth = (context: APIContext) => {
  const auth = context.request.headers.get('authorization');
  if (!auth?.startsWith('Bearer ')) throw apiError('Unauthorized', 401);
  try {
    return verifyAccessToken(auth.slice(7));
  } catch {
    throw apiError('Invalid token', 401);
  }
};
