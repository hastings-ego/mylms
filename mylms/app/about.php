<?php
require_once 'config/functions.php';

renderPublicLayoutStart(
    'About',
    'Learn the story, mission, and impact behind Fun Maths Mastery.',
    'about',
    '/about.php'
);
?>
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-brand-600 font-semibold uppercase tracking-wide text-sm mb-3">Our story</p>
                    <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight">Making math feel less intimidating.</h1>
                    <p class="mt-6 text-lg text-slate-600">Fun Maths Mastery was built around one simple idea: students learn better when math is explained clearly, visually, and with encouragement.</p>
                    <p class="mt-4 text-slate-600">We combine structured lessons, practical resources, and confidence-building support so learners can grow at their own pace.</p>
                    <div class="mt-8 grid grid-cols-3 gap-4">
                        <div>
                            <div class="text-3xl font-bold text-brand-600">300+</div>
                            <div class="text-slate-500 text-sm">Students helped</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-brand-600">98%</div>
                            <div class="text-slate-500 text-sm">Pass rate</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-brand-600">5+</div>
                            <div class="text-slate-500 text-sm">Experts</div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-3xl p-8 border border-slate-200">
                    <div class="w-20 h-20 rounded-2xl bg-brand-100 flex items-center justify-center text-brand-600 text-4xl font-bold mb-6">Σ</div>
                    <p class="text-slate-700 text-lg italic">"Through motivation, guidance, and encouragement, we help teenagers believe in their abilities and achieve their best."</p>
                    <p class="mt-4 font-semibold text-slate-900">Nosipho Hermanes, Founder</p>
                </div>
            </div>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
