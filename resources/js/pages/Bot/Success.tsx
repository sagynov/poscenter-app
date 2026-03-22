import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

// ---------------------------------------------------------------------------
// Telegram
// ---------------------------------------------------------------------------

const tg = window.Telegram?.WebApp;

const isMobile = ['android', 'ios'].includes(tg?.platform ?? '');

// ---------------------------------------------------------------------------
// Animated checkmark SVG
// ---------------------------------------------------------------------------

function AnimatedCheck() {
    return (
        <svg
            viewBox="0 0 80 80"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            style={{ width: 80, height: 80 }}
        >
            <style>{`
                @keyframes circleAnim {
                    from { stroke-dashoffset: 251; opacity: 0; }
                    to   { stroke-dashoffset: 0;   opacity: 1; }
                }
                @keyframes checkAnim {
                    from { stroke-dashoffset: 60; opacity: 0; }
                    to   { stroke-dashoffset: 0;  opacity: 1; }
                }
                .circle-path {
                    stroke-dasharray: 251;
                    stroke-dashoffset: 251;
                    animation: circleAnim 0.6s cubic-bezier(0.4,0,0.2,1) 0.1s forwards;
                }
                .check-path {
                    stroke-dasharray: 60;
                    stroke-dashoffset: 60;
                    animation: checkAnim 0.4s cubic-bezier(0.4,0,0.2,1) 0.6s forwards;
                }
            `}</style>
            <circle
                className="circle-path"
                cx="40"
                cy="40"
                r="36"
                stroke="var(--tg-button-color)"
                strokeWidth="4"
                fill="none"
                strokeLinecap="round"
            />
            <polyline
                className="check-path"
                points="24,42 35,53 56,30"
                stroke="var(--tg-button-color)"
                strokeWidth="4"
                fill="none"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

// ---------------------------------------------------------------------------
// Page
// ---------------------------------------------------------------------------

export default function Success() {
    const [visible, setVisible] = useState(false);
    const qrBase64 = sessionStorage.getItem('kaspi_qr');
    if (qrBase64) {
        sessionStorage.removeItem('kaspi_qr');
    }
    const kaspiUrl = sessionStorage.getItem('kaspi_url');
    if (kaspiUrl) {
        sessionStorage.removeItem('kaspi_url');
    }

    useEffect(() => {
        // Trigger entrance animation
        const t = setTimeout(() => setVisible(true), 50);

        if (!tg) return () => clearTimeout(t);

        tg.BackButton.hide();
        tg.MainButton.hide();
        tg.ready();
        tg.HapticFeedback?.notificationOccurred('success');

        return () => clearTimeout(t);
    }, []);

    function goHome() {
        tg?.HapticFeedback?.impactOccurred('light');
        router.visit('/bot/webapp');
    }

    return (
        <>
            <Head title="Заказ оформлен" />

            <div
                className="flex min-h-screen flex-col items-center justify-between px-6 py-10"
                style={{
                    background: 'var(--tg-bg-color)',
                    color: 'var(--tg-text-color)',
                }}
            >
                {/* Top spacer */}
                <div />

                {/* Center content */}
                <div
                    className="flex flex-col items-center gap-6 text-center"
                    style={{
                        opacity: visible ? 1 : 0,
                        transform: visible
                            ? 'translateY(0)'
                            : 'translateY(20px)',
                        transition: 'opacity 0.5s ease, transform 0.5s ease',
                    }}
                >
                    <AnimatedCheck />

                    <div className="flex flex-col gap-2">
                        <h1 className="text-2xl font-bold">Заказ оформлен!</h1>
                        <p
                            className="text-sm leading-relaxed"
                            style={{ color: 'var(--tg-hint-color)' }}
                        >
                            Ваш заказ успешно принят.{'\n'}
                            Оплатите удобным способом:
                        </p>
                    </div>

                    <div>
                        {qrBase64 && !isMobile && (
                            <img
                                src={qrBase64}
                                className="h-48 w-48 rounded-xl"
                            />
                        )}
                        {kaspiUrl && isMobile && (
                            <a
                                href={kaspiUrl}
                                onClick={(e) => {
                                    e.preventDefault();
                                    tg?.openLink(kaspiUrl!);
                                }}
                                className="block w-full rounded-2xl py-3 text-center font-semibold"
                                style={{
                                    background: 'var(--tg-button-color)',
                                    color: 'var(--tg-button-text-color)',
                                    paddingLeft: 5,
                                    paddingRight: 5,
                                }}
                            >
                                Оплатить в Kaspi →
                            </a>
                        )}
                    </div>

                    {/* Order info card */}
                    <div
                        className="w-full rounded-2xl px-5 py-4 text-left text-sm"
                        style={{ background: 'var(--tg-secondary-bg-color)' }}
                    >
                        <div
                            className="mb-3 text-xs font-semibold tracking-wider uppercase"
                            style={{ color: 'var(--tg-hint-color)' }}
                        >
                            Что дальше?
                        </div>
                        <ul className="flex flex-col gap-3">
                            {[
                                {
                                    icon: '📦',
                                    text: 'Мы подготовим ваш заказ к отправке',
                                },
                                {
                                    icon: '💬',
                                    text: 'Менеджер свяжется с вами для подтверждения',
                                },
                                {
                                    icon: '🚚',
                                    text: 'Доставка займёт 2–5 рабочих дней',
                                },
                            ].map(({ icon, text }) => (
                                <li
                                    key={text}
                                    className="flex items-start gap-3"
                                >
                                    <span className="text-base leading-snug">
                                        {icon}
                                    </span>
                                    <span
                                        style={{
                                            color: 'var(--tg-text-color)',
                                        }}
                                    >
                                        {text}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>

                {/* Bottom button */}
                <div
                    className="w-full"
                    style={{
                        opacity: visible ? 1 : 0,
                        transform: visible
                            ? 'translateY(0)'
                            : 'translateY(20px)',
                        transition:
                            'opacity 0.5s ease 0.3s, transform 0.5s ease 0.3s',
                    }}
                >
                    <button
                        onClick={goHome}
                        className="w-full rounded-2xl py-3 font-semibold"
                        style={{
                            background: 'var(--tg-button-color)',
                            color: 'var(--tg-button-text-color)',
                        }}
                    >
                        На главную
                    </button>
                </div>
            </div>
        </>
    );
}
