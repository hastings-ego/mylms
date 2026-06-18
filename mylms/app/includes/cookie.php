<?php
$cookieConsent = $_COOKIE['cookie_consent'] ?? '';

if ($cookieConsent === 'accepted' || $cookieConsent === 'declined') {
    return;
}
?>
<style>
    .cookie-consent-card {
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
        backdrop-filter: blur(8px);
    }
</style>
<div id="cookie-consent" class="fixed bottom-4 left-4 right-4 z-50 sm:left-6 sm:right-auto sm:max-w-md">
    <div class="cookie-consent-card relative rounded-2xl border border-slate-200 bg-white/95 p-5 text-slate-900">
        <button
            type="button"
            class="absolute right-3 top-3 rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
            aria-label="Dismiss cookie notice"
            data-cookie-action="cancel"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="pr-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand-600">Cookie consent</p>
            <h2 class="mt-2 text-lg font-bold text-slate-900">Help us remember your preferences</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                We use essential cookies to keep your session secure and remember your consent choice.
                You can accept non-essential cookies or cancel to keep only the cookies needed for the site to work.
            </p>
        </div>

        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
            <button
                type="button"
                class="inline-flex w-full items-center justify-center rounded-lg bg-brand-600 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-brand-700"
                data-cookie-action="accept"
            >
                Accept
            </button>
            <button
                type="button"
                class="inline-flex w-full items-center justify-center rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100"
                data-cookie-action="cancel"
            >
                Cancel
            </button>
            <a
                href="cookie-policy.php"
                class="inline-flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-semibold text-brand-700 transition-colors hover:bg-brand-50"
            >
                Read policy
            </a>
        </div>
    </div>
</div>

<script>
    (function () {
        const banner = document.getElementById('cookie-consent');
        if (!banner) return;

        const cookieName = 'cookie_consent';
        const maxAge = 60 * 60 * 24 * 365;
        const secureSuffix = window.location.protocol === 'https:' ? '; Secure' : '';

        function writeConsent(value) {
            document.cookie = cookieName + '=' + value + '; Max-Age=' + maxAge + '; Path=/; SameSite=Lax' + secureSuffix;
            banner.remove();
        }

        banner.querySelectorAll('[data-cookie-action]').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.getAttribute('data-cookie-action');
                if (action === 'accept') {
                    writeConsent('accepted');
                } else {
                    writeConsent('declined');
                }
            });
        });
    })();
</script>
