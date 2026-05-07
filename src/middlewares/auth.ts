import type { APIContext } from 'astro';
import { verifyToken } from '../lib/jwt';
export const requireAuth = (context: APIContext) => {
  const auth = context.request.headers.get('authorization');
  if (!auth?.startsWith('Bearer ')) throw new Error('Unauthorized');
  return verifyToken(auth.slice(7));
};
