import { usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function FlashMessage() {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        // Check for the existence of flash and flash.message before proceeding.
        if (flash && flash.message) {
            setVisible(true);

            const timer = setTimeout(() => {
                setVisible(false);
            }, 3000);

            return () => clearTimeout(timer);
        }
    }, [flash]);

    if (!visible) {
        return null;
    }

    return (
        <div className="fixed top-5 right-5 z-50">
            <div className="animate-fade-in-down w-full max-w-xs rounded-lg bg-emerald-500 p-4 text-white shadow-lg">
                <div className="flex items-start">
                    <div className="flex-1">
                        <p className="font-bold">Success!</p>
                        <p className="mt-1 text-sm">{flash.message}</p>
                    </div>
                    <button
                        onClick={() => setVisible(false)}
                        className="ml-3 inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-emerald-100 hover:bg-emerald-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400"
                    >
                        <span className="sr-only">Close</span>
                        <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fillRule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clipRule="evenodd"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    );
}
