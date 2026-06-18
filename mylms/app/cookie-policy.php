<?php
require_once 'config/functions.php';

renderPublicLayoutStart(
    'Cookie Policy',
    'Learn which cookies we use and how you can manage your preferences on Fun Maths Mastery.',
    'home',
    '/cookie-policy.php'
);
?>
    <section class="py-20 bg-slate-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-wide text-brand-600">Cookie Policy</p>
                <h1 class="mt-3 text-4xl font-extrabold text-slate-900">How we use cookies</h1>
                <p class="mt-4 text-lg text-slate-600">
                    Cookies help the site stay secure, remember your session, and save your consent preference.
                    We keep this policy focused on the cookies currently used by the platform.
                </p>
            </div>

            <div class="mt-10 space-y-8 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">1. Essential cookies</h2>
                    <p class="mt-3 leading-7 text-slate-700">
                        Essential cookies keep you signed in, protect forms with session and security data, and remember
                        whether you accepted or declined this notice.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-slate-900">2. Non-essential cookies</h2>
                    <p class="mt-3 leading-7 text-slate-700">
                        At the moment, we do not rely on advertising or analytics cookies in the core site experience.
                        If that changes, this page should be updated before any new non-essential cookies are added.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-slate-900">3. Managing your choice</h2>
                    <p class="mt-3 leading-7 text-slate-700">
                        You can accept the banner to allow future non-essential cookies, or cancel to keep only the
                        cookies needed for the site to function. You can also clear cookies in your browser at any time.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-slate-900">4. Contact</h2>
                    <p class="mt-3 leading-7 text-slate-700">
                        If you have questions about cookies or your privacy settings, use the contact page to reach us.
                    </p>
                </div>
            </div>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
