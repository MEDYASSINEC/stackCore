export type Role = 'ADMIN' | 'CLIENT' | 'SUPPLIER';

export interface AuthPayload {
  userId: string;
  role: Role;
  email: string;
}

export interface ApiSuccess<T> {
  data: T;
  meta?: Record<string, unknown>;
}

export interface PaginationMeta {
  page: number;
  limit: number;
  total: number;
  totalPages: number;
}
