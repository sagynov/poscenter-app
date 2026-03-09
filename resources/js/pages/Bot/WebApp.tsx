import { Head, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface Category {
    id: number;
    name: string;
    slug: string;
    image: string | null;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    old_price: number | null;
    image: string | null;
    in_stock: boolean;
    category: string;
}

interface CartItem {
    product: Product;
    quantity: number;
}

interface Props {
    categories: Category[];
    products: Product[];
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

const tg = window.Telegram?.WebApp;

function formatPrice(n: number) {
    return n.toLocaleString('ru-RU') + ' ₽';
}

// ---------------------------------------------------------------------------
// Sub-components
// ---------------------------------------------------------------------------

function CategoryPill({
    cat,
    active,
    onClick,
}: {
    cat: Category | { id: 0; name: string; slug: '' };
    active: boolean;
    onClick: () => void;
}) {
    return (
        <button
            onClick={onClick}
            className="shrink-0 rounded-full px-4 py-1.5 text-sm font-medium transition-all"
            style={{
                background: active
                    ? 'var(--tg-button-color)'
                    : 'var(--tg-secondary-bg-color)',
                color: active
                    ? 'var(--tg-button-text-color)'
                    : 'var(--tg-text-color)',
            }}
        >
            {cat.name}
        </button>
    );
}

function ProductCard({
    product,
    inCart,
    onAdd,
}: {
    product: Product;
    inCart: boolean;
    onAdd: () => void;
}) {
    return (
        <div
            className="flex flex-col overflow-hidden rounded-2xl"
            style={{ background: 'var(--tg-secondary-bg-color)' }}
        >
            {/* Image */}
            <div className="relative aspect-square overflow-hidden bg-black/5">
                {product.image ? (
                    <img
                        src={product.image}
                        alt={product.name}
                        className="h-full w-full object-cover"
                        loading="lazy"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center opacity-20">
                        <svg
                            width="48"
                            height="48"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                        >
                            <path d="M4 4h16v2H4zm0 3h16v13H4zm2 2v9h12V9z" />
                        </svg>
                    </div>
                )}
                {!product.in_stock && (
                    <div className="absolute inset-0 flex items-center justify-center bg-black/40">
                        <span className="text-xs font-semibold tracking-wide text-white uppercase">
                            Нет в наличии
                        </span>
                    </div>
                )}
                {product.old_price && (
                    <div
                        className="absolute top-2 left-2 rounded-full px-2 py-0.5 text-xs font-bold"
                        style={{ background: '#ef4444', color: '#fff' }}
                    >
                        −
                        {Math.round(
                            (1 - product.price / product.old_price) * 100,
                        )}
                        %
                    </div>
                )}
            </div>

            {/* Info */}
            <div className="flex flex-1 flex-col gap-2 p-3">
                <p
                    className="line-clamp-2 flex-1 text-sm leading-tight font-medium"
                    style={{ color: 'var(--tg-text-color)' }}
                >
                    {product.name}
                </p>

                <div className="flex items-end justify-between gap-1">
                    <div>
                        <div
                            className="text-base font-bold"
                            style={{ color: 'var(--tg-text-color)' }}
                        >
                            {formatPrice(product.price)}
                        </div>
                        {product.old_price && (
                            <div
                                className="text-xs line-through"
                                style={{ color: 'var(--tg-hint-color)' }}
                            >
                                {formatPrice(product.old_price)}
                            </div>
                        )}
                    </div>

                    <button
                        disabled={!product.in_stock}
                        onClick={onAdd}
                        className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full transition-transform active:scale-90 disabled:opacity-30"
                        style={{
                            background: inCart
                                ? '#22c55e'
                                : 'var(--tg-button-color)',
                            color: 'var(--tg-button-text-color)',
                        }}
                    >
                        {inCart ? (
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="2.5"
                            >
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        ) : (
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="2.5"
                            >
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}

// ---------------------------------------------------------------------------
// Main page
// ---------------------------------------------------------------------------

export default function WebApp({ categories, products }: Props) {
    const [activeCategory, setActiveCategory] = useState<string>('');
    const [cart, setCart] = useState<CartItem[]>([]);
    const [search, setSearch] = useState('');
    const categoryBarRef = useRef<HTMLDivElement>(null);

    const allCategories = [{ id: 0, name: 'Все', slug: '' }, ...categories];

    // Telegram MainButton for checkout
    useEffect(() => {
        if (!tg) return;

        const totalCount = cart.reduce((s, i) => s + i.quantity, 0);
        const totalPrice = cart.reduce(
            (s, i) => s + i.quantity * i.product.price,
            0,
        );

        if (totalCount > 0) {
            tg.MainButton.setText(
                `Корзина · ${totalCount} шт. · ${formatPrice(totalPrice)}`,
            );
            tg.MainButton.show();
            tg.MainButton.enable();
        } else {
            tg.MainButton.hide();
        }

        const handleCheckout = () => {
            router.visit('/bot/webapp/cart', {
                method: 'get',
                data: {
                    items: JSON.stringify(
                        cart.map((i) => ({
                            id: i.product.id,
                            qty: i.quantity,
                        })),
                    ),
                },
            });
        };

        tg.MainButton.onClick(handleCheckout);
        return () => {
            tg.MainButton.offClick(handleCheckout);
        };
    }, [cart]);

    // Telegram BackButton — скрыта на главной
    useEffect(() => {
        if (!tg) return;
        tg.BackButton.hide();
        tg.ready();
        tg.expand();
    }, []);

    // Filtered products
    const filtered = products.filter((p) => {
        const matchCat = activeCategory === '' || p.category === activeCategory;
        const matchSearch =
            search === '' ||
            p.name.toLowerCase().includes(search.toLowerCase());
        return matchCat && matchSearch;
    });

    function toggleCart(product: Product) {
        setCart((prev) => {
            const exists = prev.find((i) => i.product.id === product.id);
            if (exists) {
                return prev.filter((i) => i.product.id !== product.id);
            }
            tg?.HapticFeedback?.impactOccurred('light');
            return [...prev, { product, quantity: 1 }];
        });
    }

    return (
        <>
            <Head title="Каталог" />

            <div
                className="flex min-h-screen flex-col"
                style={{
                    background: 'var(--tg-bg-color)',
                    color: 'var(--tg-text-color)',
                }}
            >
                {/* ── Header ── */}
                <div
                    className="sticky top-0 z-10 flex flex-col gap-2 px-4 pt-3 pb-2"
                    style={{ background: 'var(--tg-bg-color)' }}
                >
                    <h1 className="text-xl font-bold tracking-tight">
                        Каталог
                    </h1>

                    {/* Search */}
                    <div
                        className="flex items-center gap-2 rounded-xl px-3 py-2"
                        style={{ background: 'var(--tg-secondary-bg-color)' }}
                    >
                        <svg
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            opacity="0.4"
                        >
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input
                            type="text"
                            placeholder="Поиск товаров..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="flex-1 bg-transparent text-sm outline-none"
                            style={{ color: 'var(--tg-text-color)' }}
                        />
                        {search && (
                            <button
                                onClick={() => setSearch('')}
                                className="opacity-40"
                            >
                                <svg
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="2"
                                >
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            </button>
                        )}
                    </div>

                    {/* Category pills */}
                    <div
                        ref={categoryBarRef}
                        className="scrollbar-none flex gap-2 overflow-x-auto pb-1"
                        style={{ scrollbarWidth: 'none' }}
                    >
                        {allCategories.map((cat) => (
                            <CategoryPill
                                key={cat.id}
                                cat={cat}
                                active={activeCategory === cat.slug}
                                onClick={() => setActiveCategory(cat.slug)}
                            />
                        ))}
                    </div>
                </div>

                {/* ── Product grid ── */}
                <div className="flex-1 px-4 pb-8">
                    {filtered.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-3 py-20 opacity-40">
                            <svg
                                width="48"
                                height="48"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="1.5"
                            >
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <p className="text-sm">Ничего не найдено</p>
                        </div>
                    ) : (
                        <div className="grid grid-cols-2 gap-3">
                            {filtered.map((product) => (
                                <ProductCard
                                    key={product.id}
                                    product={product}
                                    inCart={cart.some(
                                        (i) => i.product.id === product.id,
                                    )}
                                    onAdd={() => toggleCart(product)}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
