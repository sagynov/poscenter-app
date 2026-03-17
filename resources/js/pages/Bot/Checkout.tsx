import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useState } from 'react';

// ---------------------------------------------------------------------------
// Telegram
// ---------------------------------------------------------------------------

const tg = window.Telegram?.WebApp;
const WebView = window.Telegram?.WebView;

// ---------------------------------------------------------------------------
// Page
// ---------------------------------------------------------------------------

export default function Checkout() {
    const [name, setName] = useState('');
    const [phone, setPhone] = useState('');
    const [city, setCity] = useState('');
    const [address, setAddress] = useState('');
    const [comment, setComment] = useState('');
    const [payment, setPayment] = useState<'kaspi'>('kaspi');

    const [loading, setLoading] = useState(false);

    // -----------------------------------------------------------------------
    // Init Telegram data
    // -----------------------------------------------------------------------

    useEffect(() => {
        if (!tg) return;

        tg.BackButton.show();
        tg.BackButton.onClick(() => router.visit('/bot/webapp/cart'));

        tg.ready();

        // имя из Telegram
        const user = tg.initDataUnsafe?.user;
        if (user) {
            const fullName = [user.first_name, user.last_name]
                .filter(Boolean)
                .join(' ');
            setName(fullName);
        }

        return () => {
            tg.BackButton.hide();
        };
    }, []);

    // -----------------------------------------------------------------------
    // Request phone
    // -----------------------------------------------------------------------

    function requestPhone() {
        if (!tg || !WebView) return;

        tg.HapticFeedback?.impactOccurred('light');

        const handler = (eventType: any, eventData: any) => {
            console.log('EVENT:', eventData);

            if (!eventData?.result) return;

            const params = new URLSearchParams(eventData.result);
            const contactRaw = params.get('contact');

            if (!contactRaw) return;

            const contact = JSON.parse(decodeURIComponent(contactRaw));

            console.log('CONTACT:', contact);

            if (contact.phone_number) {
                setPhone(contact.phone_number);
            }

            WebView.offEvent('custom_method_invoked', handler);
        };

        // ✅ ВАЖНО: WebView, не WebApp
        WebView.onEvent('custom_method_invoked', handler);

        tg.requestContact();
    }

    // -----------------------------------------------------------------------
    // Submit
    // -----------------------------------------------------------------------

    async function handleSubmit() {
        if (!name || !phone || !city || !address) {
            tg?.HapticFeedback?.notificationOccurred('error');
            alert('Заполните обязательные поля');
            return;
        }

        setLoading(true);

        try {
            await axios.post('/api/checkout', {
                name,
                phone,
                city,
                address,
                comment,
                payment,
            });

            tg?.HapticFeedback?.notificationOccurred('success');

            router.visit('/bot/webapp/success');
        } catch (e) {
            tg?.HapticFeedback?.notificationOccurred('error');
        } finally {
            setLoading(false);
        }
    }

    // -----------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------

    return (
        <>
            <Head title="Оформление заказа" />

            <div
                className="flex min-h-screen flex-col px-4 py-4"
                style={{
                    background: 'var(--tg-bg-color)',
                    color: 'var(--tg-text-color)',
                }}
            >
                <h1 className="mb-4 text-lg font-bold">Оформление заказа</h1>
                <div className="flex-1">
                    <div className="flex flex-col gap-4">
                        {/* Name */}
                        <input
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            placeholder="Ваше имя"
                            className="rounded-xl px-4 py-3 text-sm outline-none"
                            style={{
                                background: 'var(--tg-secondary-bg-color)',
                            }}
                        />

                        {/* Phone */}
                        <div className="flex gap-2">
                            <input
                                value={phone}
                                onChange={(e) => setPhone(e.target.value)}
                                placeholder="Номер телефона"
                                className="flex-1 rounded-xl px-4 py-3 text-sm outline-none"
                                style={{
                                    background: 'var(--tg-secondary-bg-color)',
                                }}
                            />

                            <button
                                onClick={requestPhone}
                                className="rounded-xl px-3 text-xs font-medium"
                                style={{
                                    background: 'var(--tg-button-color)',
                                    color: 'var(--tg-button-text-color)',
                                }}
                            >
                                Мой номер
                            </button>
                        </div>

                        {/* City */}
                        <input
                            value={city}
                            onChange={(e) => setCity(e.target.value)}
                            placeholder="Город"
                            className="rounded-xl px-4 py-3 text-sm outline-none"
                            style={{
                                background: 'var(--tg-secondary-bg-color)',
                            }}
                        />

                        {/* Address */}
                        <input
                            value={address}
                            onChange={(e) => setAddress(e.target.value)}
                            placeholder="Адрес доставки"
                            className="rounded-xl px-4 py-3 text-sm outline-none"
                            style={{
                                background: 'var(--tg-secondary-bg-color)',
                            }}
                        />

                        {/* Comment */}
                        <textarea
                            value={comment}
                            onChange={(e) => setComment(e.target.value)}
                            placeholder="Комментарий к заказу"
                            rows={3}
                            className="rounded-xl px-4 py-3 text-sm outline-none"
                            style={{
                                background: 'var(--tg-secondary-bg-color)',
                            }}
                        />

                        {/* Payment */}
                        <div className="flex flex-col gap-2">
                            <p
                                className="text-sm"
                                style={{ color: 'var(--tg-hint-color)' }}
                            >
                                Способ оплаты
                            </p>

                            <div
                                onClick={() => setPayment('kaspi')}
                                className="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3"
                                style={{
                                    background: 'var(--tg-secondary-bg-color)',
                                }}
                            >
                                <span>Kaspi</span>
                                {payment === 'kaspi' && '✓'}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Submit */}
                <button
                    onClick={handleSubmit}
                    disabled={loading}
                    className="mt-4 rounded-2xl py-3 font-semibold transition-opacity disabled:opacity-60"
                    style={{
                        background: 'var(--tg-button-color)',
                        color: 'var(--tg-button-text-color)',
                    }}
                >
                    {loading ? 'Обработка...' : 'Оплатить'}
                </button>
            </div>
        </>
    );
}
