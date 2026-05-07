import type { Role } from '../types/domain';
import { HttpError } from './auth';

export const requireRole = (userRole: Role, allowed: Role[]) => {
  if (!allowed.includes(userRole)) throw new HttpError(403, 'Forbidden');
};
