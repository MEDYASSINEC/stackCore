import type { APIRoute } from 'astro';
import { prisma } from '../../../lib/prisma';
import { requireAuth } from '../../../middlewares/auth';
import { requireRole } from '../../../middlewares/rbac';
import { productSchema } from '../../../services/validation';
import { handleApiError } from '../../../utils/errors';

export const GET: APIRoute = async ({ url }) => {
  const page = Number(url.searchParams.get('page') ?? 1);
  const limit = Math.min(Number(url.searchParams.get('limit') ?? 12), 50);
  const q = url.searchParams.get('q') ?? undefined;
  const category = url.searchParams.get('category') ?? undefined;
  const where = {
    ...(q ? { name: { contains: q, mode: 'insensitive' as const } } : {}),
    ...(category ? { category } : {})
  };
  const [total, data] = await Promise.all([
    prisma.product.count({ where }),
    prisma.product.findMany({ where, skip: (page - 1) * limit, take: limit, orderBy: { createdAt: 'desc' } })
  ]);
  return new Response(JSON.stringify({ data, meta: { page, limit, total, totalPages: Math.ceil(total / limit) } }));
};

export const POST: APIRoute = async (ctx) => {
  try {
    const auth = requireAuth(ctx);
    requireRole(auth.role, ['ADMIN', 'SUPPLIER']);
    const body = productSchema.parse(await ctx.request.json());
    const product = await prisma.product.create({ data: body });
    return new Response(JSON.stringify({ data: product }), { status: 201 });
  } catch (error) {
    return handleApiError(error);
  }
};
