import { PrismaClient, Role, OrderStatus, RecyclingStatus } from "@prisma/client";

const prisma = new PrismaClient();

async function main() {
  // USERS
  const admin = await prisma.user.create({
    data: {
      email: "admin@stackcore.com",
      passwordHash: "hashed-password",
      role: Role.ADMIN,
      loyaltyPoints: 500,
    },
  });

  const client = await prisma.user.create({
    data: {
      email: "client@stackcore.com",
      passwordHash: "hashed-password",
      role: Role.CLIENT,
      loyaltyPoints: 120,
    },
  });

  const supplier = await prisma.user.create({
    data: {
      email: "supplier@stackcore.com",
      passwordHash: "hashed-password",
      role: Role.SUPPLIER,
      loyaltyPoints: 0,
    },
  });

  // PRODUCTS
  const laptop = await prisma.product.create({
    data: {
      name: "Dell XPS 15",
      description: "High-performance laptop for developers",
      price: 1899.99,
      stock: 15,
      imageUrl: "https://images.unsplash.com/photo-1496181133206-80ce9b88a853",
      category: "Laptops",
    },
  });

  const mouse = await prisma.product.create({
    data: {
      name: "Logitech MX Master 3",
      description: "Wireless productivity mouse",
      price: 99.99,
      stock: 40,
      imageUrl: "https://images.unsplash.com/photo-1527814050087-3793815479db",
      category: "Accessories",
    },
  });

  const keyboard = await prisma.product.create({
    data: {
      name: "Keychron K2",
      description: "Mechanical wireless keyboard",
      price: 89.99,
      stock: 25,
      imageUrl: "https://images.unsplash.com/photo-1511467687858-23d96c32e4ae",
      category: "Accessories",
    },
  });

  // ORDER
  const order = await prisma.order.create({
    data: {
      userId: client.id,
      status: OrderStatus.PAID,
      total: 2089.98,
      items: {
        create: [
          {
            productId: laptop.id,
            quantity: 1,
            unitPrice: 1899.99,
          },
          {
            productId: mouse.id,
            quantity: 2,
            unitPrice: 99.99,
          },
        ],
      },
    },
  });

  // RECYCLING REQUEST
  await prisma.recyclingRequest.create({
    data: {
      userId: client.id,
      description: "Old laptop battery and broken keyboard",
      photoUrl: "https://images.unsplash.com/photo-1518770660439-4636190af475",
      status: RecyclingStatus.APPROVED,
      rewardPoints: 75,
    },
  });

  console.log("✅ Seed data inserted");
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });