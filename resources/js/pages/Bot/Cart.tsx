import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useRef, useState } from 'react';

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    image: string | null;
}

export interface CartItem {
    id: number;
    product: Product;
    quantity: number;
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

const tg = window.Telegram?.WebApp;

function formatPrice(n: number) {
    return n.toLocaleString('ru-RU') + ' ₸';
}

const MIN_ASTANA_AMOUNT = 25_000;

// ---------------------------------------------------------------------------
// QuantityControl
// ---------------------------------------------------------------------------

function QuantityControl({
    value,
    onChange,
    onRemove,
}: {
    value: number;
    onChange: (v: number) => void;
    onRemove: () => void;
}) {
    return (
        <div className="flex items-center gap-1">
            <button
                onClick={() => (value <= 1 ? onRemove() : onChange(value - 1))}
                className="flex h-7 w-7 items-center justify-center rounded-lg transition-opacity active:opacity-60"
                style={{ background: 'var(--tg-secondary-bg-color)' }}
            >
                {value <= 1 ? (
                    // trash icon
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        style={{ color: '#ef4444' }}
                    >
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14H6L5 6" />
                        <path d="M10 11v6M14 11v6" />
                        <path d="M9 6V4h6v2" />
                    </svg>
                ) : (
                    <svg
                        width="13"
                        height="13"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2.5"
                    >
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                )}
            </button>

            <span
                className="w-6 text-center text-sm font-semibold"
                style={{ color: 'var(--tg-text-color)' }}
            >
                {value}
            </span>

            <button
                onClick={() => onChange(value + 1)}
                className="flex h-7 w-7 items-center justify-center rounded-lg transition-opacity active:opacity-60"
                style={{ background: 'var(--tg-secondary-bg-color)' }}
            >
                <svg
                    width="13"
                    height="13"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2.5"
                >
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
            </button>
        </div>
    );
}

// ---------------------------------------------------------------------------
// CartItemRow
// ---------------------------------------------------------------------------

function CartItemRow({
    item,
    onQuantityChange,
    onRemove,
}: {
    item: CartItem;
    onQuantityChange: (id: number, qty: number) => void;
    onRemove: (id: number) => void;
}) {
    return (
        <div
            className="flex items-center gap-3 py-3"
            style={{
                borderBottom:
                    '1px solid color-mix(in srgb, var(--tg-hint-color) 20%, transparent)',
            }}
        >
            {/* Image */}
            <div
                className="h-14 w-14 shrink-0 overflow-hidden rounded-xl"
                style={{ background: 'var(--tg-secondary-bg-color)' }}
            >
                {item.product.image ? (
                    <img
                        src={`/storage/${item.product.image}`}
                        alt={item.product.name}
                        className="h-full w-full object-cover"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center opacity-20">
                        <svg
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                        >
                            <path d="M4 4h16v2H4zm0 3h16v13H4zm2 2v9h12V9z" />
                        </svg>
                    </div>
                )}
            </div>

            {/* Name + price */}
            <div className="min-w-0 flex-1">
                <p
                    className="line-clamp-2 text-sm leading-snug font-medium"
                    style={{ color: 'var(--tg-text-color)' }}
                >
                    {item.product.name}
                </p>
                <p
                    className="mt-0.5 text-sm"
                    style={{ color: 'var(--tg-hint-color)' }}
                >
                    {formatPrice(item.product.price)} × {item.quantity}
                </p>
            </div>

            {/* Right side */}
            <div className="flex shrink-0 flex-col items-end gap-1.5">
                <span
                    className="text-sm font-bold"
                    style={{ color: 'var(--tg-text-color)' }}
                >
                    {formatPrice(item.product.price * item.quantity)}
                </span>

                <QuantityControl
                    value={item.quantity}
                    onChange={(qty) => onQuantityChange(item.id, qty)}
                    onRemove={() => onRemove(item.id)}
                />
            </div>
        </div>
    );
}

// ---------------------------------------------------------------------------
// Main page
// ---------------------------------------------------------------------------

export default function Cart() {
    const [items, setItems] = useState<CartItem[]>([]);
    const [fromAstana, setFromAstana] = useState(false);
    const [paying, setPaying] = useState(false);
    const initialItemsRef = useRef<CartItem[]>([]);
    const [loading, setLoading] = useState(true);

    const total = items.reduce((s, i) => s + i.product.price * i.quantity, 0);
    const showPayButton = fromAstana && total >= MIN_ASTANA_AMOUNT;

    useEffect(() => {
        axios
            .get('/api/cart')
            .then(({ data }) => {
                setItems(data.data);
                initialItemsRef.current = data.data;
            })
            .finally(() => setLoading(false));
    }, []);

    // Telegram setup
    useEffect(() => {
        if (!tg) return;

        const handleBack = () => router.visit('/bot/webapp');
        tg.BackButton.show();
        tg.BackButton.offClick(handleBack); // сначала снимаем
        tg.BackButton.onClick(handleBack);
        tg.ready();

        return () => {
            tg.BackButton.offClick(handleBack); // сначала снимаем
            tg.BackButton.hide();
        };
    }, []);

    async function handleQuantityChange(id: number, qty: number) {
        tg?.HapticFeedback?.impactOccurred('light');
        await axios.patch(`/api/cart/${id}`, { quantity: qty });
        setItems((prev) =>
            prev.map((i) => (i.id === id ? { ...i, quantity: qty } : i)),
        );
    }

    function handleRemove(id: number) {
        tg?.HapticFeedback?.impactOccurred('medium');
        setItems((prev) => prev.filter((i) => i.id !== id));
        axios.delete(`/api/cart/${id}`);
    }

    function handlePay() {
        setPaying(true);
        tg?.HapticFeedback?.notificationOccurred('success');
        router.visit('/bot/webapp/checkout');
    }
    if (loading) {
        return (
            <div
                className="flex min-h-screen items-center justify-center"
                style={{ background: 'var(--tg-bg-color)' }}
            >
                <div className="h-6 w-6 animate-spin rounded-full border-2 border-current border-t-transparent opacity-40" />
            </div>
        );
    }
    if (items.length === 0) {
        return (
            <>
                <Head title="Ваш заказ" />
                <div
                    className="flex min-h-screen flex-col items-center justify-center gap-4 px-6"
                    style={{
                        background: 'var(--tg-bg-color)',
                        color: 'var(--tg-text-color)',
                    }}
                >
                    <svg
                        width="64"
                        height="64"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="1.2"
                        opacity="0.25"
                    >
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <path d="M16 10a4 4 0 01-8 0" />
                    </svg>
                    <p className="text-base font-medium opacity-40">
                        Корзина пуста
                    </p>
                    <button
                        onClick={() => router.visit('/bot/webapp')}
                        className="text-sm font-medium"
                        style={{ color: 'var(--tg-link-color)' }}
                    >
                        Вернуться в каталог
                    </button>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Ваш заказ" />

            <div
                className="flex min-h-screen flex-col"
                style={{
                    background: 'var(--tg-bg-color)',
                    color: 'var(--tg-text-color)',
                }}
            >
                {/* ── Header ── */}
                <div
                    className="sticky top-0 z-10 flex items-center justify-between px-4 py-3"
                    style={{
                        background: 'var(--tg-bg-color)',
                    }}
                >
                    <h1 className="text-lg font-bold">Ваш заказ</h1>
                </div>

                {/* Подсказка почему нельзя оплатить */}
                {total < MIN_ASTANA_AMOUNT ? (
                    <div
                        className="mx-4 flex items-center gap-3 rounded-md p-3 text-xs"
                        style={{
                            background: '#E1F4E4',
                            color: 'var(--tg-button-color)',
                        }}
                    >
                        <img
                            src="/images/attention_green.svg"
                            alt=""
                            className="h-5 w-5 shrink-0"
                        />
                        <span>
                            {`Для оформления заказа добавьте товаров на сумму не менее ${formatPrice(MIN_ASTANA_AMOUNT)}`}
                        </span>
                    </div>
                ) : null}

                {/* ── Items ── */}
                <div className="flex-1">
                    <div className="px-4">
                        {items.map((item) => (
                            <CartItemRow
                                key={item.id}
                                item={item}
                                onQuantityChange={handleQuantityChange}
                                onRemove={handleRemove}
                            />
                        ))}
                    </div>
                    {/* Подсказка почему нельзя оплатить */}
                    {!fromAstana ? (
                        <div
                            className="mx-4 mt-3 flex items-center gap-3 rounded-md p-3 text-xs"
                            style={{
                                background: 'var(--tg-danger-bg-color)',
                                color: 'var(--tg-danger-text-color)',
                            }}
                        >
                            <img
                                src="/images/attention_red.svg"
                                alt=""
                                className="h-5 w-5 shrink-0"
                            />
                            <span>
                                Пожалуйста, учтите: пока мы работаем только по
                                Астане.
                            </span>
                        </div>
                    ) : null}
                </div>

                {/* ── Footer ── */}
                <div
                    className="flex flex-col gap-4 px-4 pt-4 pb-8"
                    style={{
                        borderTop:
                            '1px solid color-mix(in srgb, var(--tg-hint-color) 15%, transparent)',
                    }}
                >
                    {/* Total */}
                    <div className="flex items-center justify-between">
                        <span
                            className="text-sm"
                            style={{ color: 'var(--tg-hint-color)' }}
                        >
                            Итого ({items.reduce((s, i) => s + i.quantity, 0)}{' '}
                            шт.)
                        </span>
                        <span className="text-lg font-bold">
                            {formatPrice(total)}
                        </span>
                    </div>

                    {/* Astana checkbox */}
                    <label className="flex cursor-pointer items-center gap-3 select-none">
                        <div
                            onClick={() => {
                                tg?.HapticFeedback?.impactOccurred('light');
                                setFromAstana((v) => !v);
                            }}
                            className="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border-2 transition-all"
                            style={{
                                borderColor: fromAstana
                                    ? 'var(--tg-button-color)'
                                    : 'var(--tg-hint-color)',
                                background: fromAstana
                                    ? 'var(--tg-button-color)'
                                    : 'transparent',
                            }}
                        >
                            {fromAstana && (
                                <svg
                                    width="11"
                                    height="11"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="var(--tg-button-text-color)"
                                    strokeWidth="3"
                                >
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            )}
                        </div>
                        <div>
                            <p
                                className="text-sm font-medium"
                                style={{ color: 'var(--tg-text-color)' }}
                            >
                                Я из Астаны
                            </p>
                        </div>
                    </label>

                    {/* Pay button */}
                    <div>
                        <button
                            onClick={handlePay}
                            disabled={!showPayButton || paying}
                            className="flex w-full items-center justify-between rounded-2xl px-5 py-3.5 font-semibold transition-opacity disabled:cursor-not-allowed"
                            style={{
                                background:
                                    !showPayButton || paying
                                        ? '#959595'
                                        : 'var(--tg-button-color)',
                                color: 'var(--tg-button-text-color)',
                            }}
                        >
                            <span>
                                {paying ? 'Обработка...' : 'Оформить заказ'}
                            </span>
                            <span>{formatPrice(total)}</span>
                        </button>
                    </div>
                </div>
            </div>
        </>
    );
}
