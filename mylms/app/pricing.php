<?php
require_once 'config/functions.php';

renderPublicLayoutStart(
    'Pricing',
    'Compare the learning plans and choose the support level that fits your goals.',
    'pricing',
    '/pricing.php'
);
?>
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-12">
                <p class="text-brand-600 font-semibold uppercase tracking-wide text-sm mb-3">Simple pricing</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight">Choose the plan that fits the learner.</h1>
                <p class="mt-5 text-lg text-slate-600">No hidden fees. Upgrade when you’re ready for more support.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-slate-50 rounded-2xl p-8 text-center border border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-900">Student Package</h2>
                    <div class="mt-4 text-5xl font-extrabold text-brand-600">R0.00</div>
                    <p class="text-slate-500 mt-2">per month</p>
                    <ul class="mt-6 space-y-3 text-slate-600 text-left">
                        <li>✓ Full access to all classrooms</li>
                        <li>✓ Unlimited practice problems</li>
                        <li>✓ Progress tracking</li>
                    </ul>
                    <a href="login.php" class="mt-8 inline-block w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg transition">Get Started</a>
                </div>

                <div class="relative bg-slate-50 rounded-2xl p-8 text-center border border-slate-200">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-brand-500 text-white text-xs font-bold px-3 py-1 rounded-full">Most Popular</div>
                    
                    <h2 class="text-2xl font-bold text-slate-900">Contributors Package</h2>
                    <div class="mt-4 text-5xl font-extrabold text-brand-600">R999.99</div>
                    <p class="text-slate-500 mt-2">Once Off</p>
                    <ul class="mt-6 space-y-3 text-slate-600 text-left">
                        <li>✓ Upload your own content</li>
                        <li>✓ Monetize your content</li>
                        <li>✓ Create worksheets</li>
                    </ul>
                    <a href="login.php" class="mt-8 inline-block w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg transition">Get Started</a>
                </div>

                <div class="bg-slate-50 rounded-2xl p-8 text-center border border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-900">Tutor Package</h2>
                    <div class="mt-4 text-5xl font-extrabold text-brand-600">R499.99</div>
                    <p class="text-slate-500 mt-2">one-time</p>
                    <ul class="mt-6 space-y-3 text-slate-600 text-left">
                        <li>✓ 1-on-1 tutoring sessions</li>
                        <li>✓ Hosts 1 Classroom</li>
                        <li>✓ Lifetime updates</li>
                    </ul>
                    <a href="tutoring.php" class="mt-8 inline-block w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg transition">Meet Tutors</a>
                </div>
            </div>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
