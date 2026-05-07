import jwt from 'jsonwebtoken';
import type { AuthPayload } from '../types/domain';
const JWT_SECRET = process.env.JWT_SECRET ?? 'dev-secret';
export const signAccessToken = (payload: AuthPayload) => jwt.sign(payload, JWT_SECRET, { expiresIn: '15m' });
export const verifyToken = (token: string) => jwt.verify(token, JWT_SECRET) as AuthPayload;
