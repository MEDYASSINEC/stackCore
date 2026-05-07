export type Role = 'ADMIN' | 'CLIENT' | 'SUPPLIER';
export interface AuthPayload { userId: string; role: Role; email: string; }
