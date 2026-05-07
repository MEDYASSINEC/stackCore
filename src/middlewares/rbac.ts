import type { Role } from '../types/domain';
export const requireRole = (userRole: Role, allowed: Role[]) => {
  if (!allowed.includes(userRole)) throw new Error('Forbidden');
};
