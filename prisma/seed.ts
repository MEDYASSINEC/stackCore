import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcrypt';
const prisma = new PrismaClient();
async function main() {
  const adminPass = await bcrypt.hash('Admin1234!', 12);
  await prisma.user.upsert({ where: { email: 'admin@stackcore.local' }, update: {}, create: { email: 'admin@stackcore.local', passwordHash: adminPass, role: 'ADMIN' } });
  await prisma.product.createMany({ data: [
    { name: 'Eco Laptop', description: 'Ordinateur reconditionné premium', price: 899, stock: 15, category: 'Informatique' },
    { name: 'Chargeur solaire', description: 'Chargeur portable écologique', price: 49, stock: 120, category: 'Accessoires' }
  ], skipDuplicates: true });
}
main().finally(() => prisma.$disconnect());
