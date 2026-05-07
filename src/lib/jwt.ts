import jwt from 'jsonwebtoken';
import type { AuthPayload } from '../types/domain';

const JWT_SECRET = process.env.JWT_SECRET ?? 'dev-secret';
const JWT_REFRESH_SECRET = process.env.JWT_REFRESH_SECRET ?? 'dev-refresh-secret';

export const signAccessToken = (payload: AuthPayload) => jwt.sign(payload, JWT_SECRET, { expiresIn: '15m' });
export const signRefreshToken = (payload: AuthPayload) => jwt.sign(payload, JWT_REFRESH_SECRET, { expiresIn: '7d' });

export const verifyAccessToken = (token: string) => jwt.verify(token, JWT_SECRET) as AuthPayload;
export const verifyRefreshToken = (token: string) => jwt.verify(token, JWT_REFRESH_SECRET) as AuthPayload;
