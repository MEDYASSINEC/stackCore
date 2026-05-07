import type { APIContext } from 'astro';
import { verifyToken } from '../lib/jwt';

export class HttpError extends Error {
  constructor(
    public readonly status: number,
    message: string
  ) {
    super(message);
  }
}

export const requireAuth = (context: APIContext) => {
  const auth = context.request.headers.get('authorization');
  if (!auth?.startsWith('Bearer ')) throw new HttpError(401, 'Unauthorized');

  try {
    return verifyToken(auth.slice(7));
  } catch {
    throw new HttpError(401, 'Unauthorized');
  }
};
