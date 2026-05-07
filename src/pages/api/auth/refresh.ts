import type { APIRoute } from 'astro';
import { refreshSchema } from '../../../services/validation';
import { signAccessToken, verifyRefreshToken } from '../../../lib/jwt';
import { handleApiError } from '../../../utils/errors';

export const POST: APIRoute = async ({ request }) => {
  try {
    const { refreshToken } = refreshSchema.parse(await request.json());
    const payload = verifyRefreshToken(refreshToken);
    return new Response(JSON.stringify({ accessToken: signAccessToken(payload) }));
  } catch (error) {
    return handleApiError(error);
  }
};
