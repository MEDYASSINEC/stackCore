import type { Role } from '../types/domain';
import { apiError } from '../utils/errors';

export const requireRole = (userRole: Role, allowed: Role[]) => {
  if (!allowed.includes(userRole)) throw apiError('Forbidden', 403);
};
