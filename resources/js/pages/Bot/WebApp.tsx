import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { useCallback, useEffect, useRef, useState } from 'react';

interface Category {
    id: number;
    name: string;
    slug: string;
    image: string;
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
    id: number;
    product: Product;
    quantity: number;
}

const tg = window.Telegram?.WebApp;

function formatPrice(n: number) {
    return n.toLocaleString('ru-RU') + ' ₸';
}

function CategoryPill({
    cat,
    active,
    onClick,
}: {
    cat: { id: number; name: string; slug: string; image: string };
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

interface ProductCardProps {
    product: Product;
    cartItem: CartItem | undefined;
    onAdd: () => void;
    onIncrement: () => void;
    onDecrement: () => void;
}

function ProductCard({
    product,
    cartItem,
    onAdd,
    onIncrement,
    onDecrement,
}: ProductCardProps) {
    return (
        <div
            className="flex flex-col overflow-hidden rounded-2xl"
            style={{ background: 'var(--tg-secondary-bg-color)' }}
        >
            <div className="relative aspect-square overflow-hidden bg-black/5">
                {product.image ? (
                    <img
                        src={`/storage/${product.image}`}
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
                        {Math.round(
                            (1 - product.price / product.old_price) * 100,
                        )}
                        %
                    </div>
                )}
            </div>

            <div className="flex flex-1 flex-col gap-3 p-3">
                <div className="flex justify-between">
                    <p
                        className="line-clamp-2 flex-1 text-left text-sm leading-tight font-medium"
                        style={{ color: 'var(--tg-text-color)' }}
                    >
                        {product.name}
                    </p>

                    <div className="text-right">
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
                </div>

                {/* Кнопка / счётчик */}
                {!cartItem ? (
                    <button
                        disabled={!product.in_stock}
                        onClick={onAdd}
                        className="w-full rounded-xl py-2 text-sm transition-opacity active:opacity-75 disabled:opacity-30"
                        style={{
                            background: 'var(--tg-text-color)',
                            color: 'var(--tg-button-text-color)',
                        }}
                    >
                        В корзину
                    </button>
                ) : (
                    <div
                        className="flex items-center justify-between overflow-hidden rounded-xl"
                        style={{ background: 'var(--tg-text-color)' }}
                    >
                        <button
                            onClick={onDecrement}
                            className="flex h-9 w-9 items-center justify-center text-lg transition-opacity active:opacity-60"
                            style={{ color: 'var(--tg-button-text-color)' }}
                        >
                            −
                        </button>
                        <span
                            className="text-sm"
                            style={{ color: 'var(--tg-button-text-color)' }}
                        >
                            {cartItem.quantity}
                        </span>
                        <button
                            onClick={onIncrement}
                            className="flex h-9 w-9 items-center justify-center text-lg transition-opacity active:opacity-60"
                            style={{ color: 'var(--tg-button-text-color)' }}
                        >
                            +
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}

function ProductSkeleton() {
    return (
        <div
            className="flex animate-pulse flex-col overflow-hidden rounded-2xl"
            style={{ background: 'var(--tg-secondary-bg-color)' }}
        >
            <div
                className="aspect-square w-full rounded"
                style={{ background: 'var(--tg-hint-color)', opacity: 0.15 }}
            />
            <div className="flex flex-col gap-2 p-3">
                <div
                    className="h-3 w-full rounded"
                    style={{
                        background: 'var(--tg-hint-color)',
                        opacity: 0.15,
                    }}
                />
                <div
                    className="h-3 w-2/3 rounded"
                    style={{
                        background: 'var(--tg-hint-color)',
                        opacity: 0.15,
                    }}
                />
                <div
                    className="h-5 w-1/2 rounded"
                    style={{
                        background: 'var(--tg-hint-color)',
                        opacity: 0.15,
                    }}
                />
            </div>
        </div>
    );
}

export default function WebApp() {
    const [categories, setCategories] = useState<Category[]>([]);
    const [products, setProducts] = useState<Product[]>([]);
    const [cart, setCart] = useState<CartItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [activeCategory, setActiveCategory] = useState<string>('');
    const [search, setSearch] = useState('');
    const categoryBarRef = useRef<HTMLDivElement>(null);

    const allCategories = [
        { id: 0, name: 'Все', slug: '', image: '' },
        ...categories,
    ];

    useEffect(() => {
        axios
            .get('/api/catalog')
            .then(({ data }) => {
                setCategories(data.categories);
                setProducts(data.products);
                setCart(data.cart);
            })
            .catch(() => setError('Не удалось загрузить каталог'))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        if (!tg) return;
        tg.BackButton.hide();
        tg.ready();
        tg.expand();
    }, []);

    const handleCheckout = useCallback(() => {
        router.visit('/bot/webapp/cart');
    }, []);

    const filtered = products.filter((p) => {
        const matchCat = activeCategory === '' || p.category === activeCategory;
        const matchSearch =
            search === '' ||
            p.name.toLowerCase().includes(search.toLowerCase());
        return matchCat && matchSearch;
    });

    async function addToCart(product: Product) {
        const { data } = await axios.post('/api/cart', {
            product_id: product.id,
            quantity: 1,
        });
        tg?.HapticFeedback?.impactOccurred('light');
        setCart((prev) => [
            ...prev,
            { id: data.data.id, product, quantity: 1 },
        ]);
    }

    async function incrementCart(product: Product) {
        const item = cart.find((i) => i.product.id === product.id);
        if (!item) return;
        await axios.patch(`/api/cart/${item.id}`, {
            quantity: item.quantity + 1,
        });
        tg?.HapticFeedback?.impactOccurred('light');
        setCart((prev) =>
            prev.map((i) =>
                i.product.id === product.id
                    ? { ...i, quantity: i.quantity + 1 }
                    : i,
            ),
        );
    }

    async function decrementCart(product: Product) {
        const item = cart.find((i) => i.product.id === product.id);
        if (!item) return;
        if (item.quantity <= 1) {
            await axios.delete(`/api/cart/${item.id}`);
            setCart((prev) => prev.filter((i) => i.product.id !== product.id));
        } else {
            await axios.patch(`/api/cart/${item.id}`, {
                quantity: item.quantity - 1,
            });
            setCart((prev) =>
                prev.map((i) =>
                    i.product.id === product.id
                        ? { ...i, quantity: i.quantity - 1 }
                        : i,
                ),
            );
        }
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
                {/* Header */}
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

                {/* Content */}
                <div className="flex-1 px-4 pb-8">
                    {/* Skeleton */}
                    {loading && (
                        <div className="grid grid-cols-2 gap-3">
                            {Array.from({ length: 6 }).map((_, i) => (
                                <ProductSkeleton key={i} />
                            ))}
                        </div>
                    )}

                    {/* Empty */}
                    {!loading && !error && filtered.length === 0 && (
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
                    )}

                    {/* Products */}
                    {!loading && !error && filtered.length > 0 && (
                        <div
                            className="grid grid-cols-2 gap-3"
                            style={{
                                paddingBottom: cart.length > 0 ? '88px' : '0px',
                            }}
                        >
                            {filtered.map((product) => (
                                <ProductCard
                                    key={product.id}
                                    product={product}
                                    cartItem={cart.find(
                                        (i) => i.product.id === product.id,
                                    )}
                                    onAdd={() => addToCart(product)}
                                    onIncrement={() => incrementCart(product)}
                                    onDecrement={() => decrementCart(product)}
                                />
                            ))}
                        </div>
                    )}
                </div>

                {/* Корзина кнопка */}
                {cart.length > 0 && (
                    <div
                        className="fixed right-0 bottom-0 left-0 p-4"
                        style={{ background: 'var(--tg-bg-color)' }}
                    >
                        <button
                            onClick={handleCheckout}
                            className="flex w-full items-center justify-between rounded-2xl px-5 py-3.5 font-semibold transition-opacity active:opacity-80"
                            style={{
                                background: 'var(--tg-button-color)',
                                color: 'var(--tg-button-text-color)',
                            }}
                        >
                            <span>Посмотреть корзину</span>
                            <span>
                                {formatPrice(
                                    cart.reduce(
                                        (s, i) =>
                                            s + i.quantity * i.product.price,
                                        0,
                                    ),
                                )}
                            </span>
                        </button>
                    </div>
                )}
            </div>
        </>
    );
}
