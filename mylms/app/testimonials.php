<?php
require_once 'config/functions.php';

renderPublicLayoutStart(
    'Testimonials',
    'Read student and parent feedback about the learning experience.',
    'testimonials',
    '/testimonials.php'
);
?>
    <section class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-12">
                <p class="text-brand-600 font-semibold uppercase tracking-wide text-sm mb-3">Success stories</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight">What students and parents say.</h1>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex items-center gap-1 text-yellow-400 mb-3">★★★★★</div>
                    <p class="text-slate-600 mb-4">“I went from failing algebra to scoring 92% on my final exam. The step-by-step videos changed everything.”</p>
                    <div class="font-semibold text-slate-900">Alex M., Grade 10</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex items-center gap-1 text-yellow-400 mb-3">★★★★★</div>
                    <p class="text-slate-600 mb-4">“Calculus finally made sense once the visuals and examples clicked. It felt less like memorizing and more like understanding.”</p>
                    <div class="font-semibold text-slate-900">Jessica L., College Student</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex items-center gap-1 text-yellow-400 mb-3">★★★★★</div>
                    <p class="text-slate-600 mb-4">“My daughter is excited about math now. The teachers are responsive and the structure is easy to follow.”</p>
                    <div class="font-semibold text-slate-900">David R., Parent</div>
                </div>
            </div>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
