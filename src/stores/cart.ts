import { create } from 'zustand';
import { persist } from 'zustand/middleware';

type CartItem = { productId: string; name: string; price: number; quantity: number };
type CartState = {
  items: CartItem[];
  addItem: (item: Omit<CartItem, 'quantity'>) => void;
  updateQty: (productId: string, quantity: number) => void;
  removeItem: (productId: string) => void;
  clear: () => void;
};

export const useCartStore = create<CartState>()(
  persist(
    (set, get) => ({
      items: [],
      addItem: (item) => {
        const found = get().items.find((x) => x.productId === item.productId);
        if (found) {
          set({ items: get().items.map((x) => x.productId === item.productId ? { ...x, quantity: x.quantity + 1 } : x) });
          return;
        }
        set({ items: [...get().items, { ...item, quantity: 1 }] });
      },
      updateQty: (productId, quantity) => set({ items: get().items.map((x) => x.productId === productId ? { ...x, quantity: Math.max(quantity, 1) } : x) }),
      removeItem: (productId) => set({ items: get().items.filter((x) => x.productId !== productId) }),
      clear: () => set({ items: [] })
    }),
    { name: 'stackcore-cart' }
  )
);
