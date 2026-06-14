<?php
require_once 'config/functions.php';

renderPublicLayoutStart(
    'Terms of Service',
    'Read the terms that apply when using Fun Maths Mastery.',
    'home',
    '/terms.php'
);
?>
    <section class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-extrabold text-slate-900">Terms of Service</h1>
            <p class="mt-4 text-slate-600">These terms govern your use of Fun Maths Mastery. By using the site, you agree to the following.</p>
            <div class="mt-8 space-y-6 text-slate-700 leading-7">
                <p>You may use the platform for personal, non-commercial learning purposes only.</p>
                <p>Account access is for the registered user only. You are responsible for keeping your password confidential.</p>
                <p>Digital resources are licensed for your own study use and may not be copied, resold, or redistributed without permission.</p>
                <p>We may update, suspend, or modify the service as needed to improve reliability or content quality.</p>
                <p>Some features, including tutoring booking and payment integration, may be simulated or under development.</p>
            </div>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
