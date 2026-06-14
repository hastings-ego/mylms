<?php
require_once 'config/functions.php';

renderPublicLayoutStart(
    'Privacy Policy',
    'Read how Fun Maths Mastery handles account and checkout information.',
    'home',
    '/privacy.php'
);
?>
    <section class="py-20 bg-slate-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-extrabold text-slate-900">Privacy Policy</h1>
            <p class="mt-4 text-slate-600">We only collect the information needed to provide your account, purchases, and support experience.</p>
            <div class="mt-8 space-y-6 text-slate-700 leading-7">
                <p>We store your name, email address, password hash, and purchase history so the platform can authenticate you and grant access to resources.</p>
                <p>Contact form submissions may be used to reply to your question and improve support.</p>
                <p>We do not sell your personal information.</p>
                <p>Digital receipts, access records, and reset tokens are used for account and security operations only.</p>
                <p>If you need help with your account data, contact us through the site contact page.</p>
            </div>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
