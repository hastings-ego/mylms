<?php
require_once 'config/functions.php';

renderPublicLayoutStart(
    'Features',
    'Explore the learning experience, practice tools, and study support built into Fun Maths Mastery.',
    'features',
    '/features.php'
);
?>
    <section class="bg-gradient-to-b from-white to-slate-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-24">
            <div class="max-w-3xl">
                <p class="text-brand-600 font-semibold uppercase tracking-wide text-sm mb-3">What makes it work</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight">Learning tools that make math feel doable.</h1>
                <p class="mt-6 text-lg text-slate-600">We blend clear explanations, visual support, and practice-first learning so students can build confidence without feeling overwhelmed.</p>
            </div>
        </div>
    </section>

    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
                    <div class="w-14 h-14 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-5">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path></svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 mb-3">Step-by-step clarity</h2>
                    <p class="text-slate-600">Every topic is broken down into smaller wins so students can understand the why behind each answer.</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
                    <div class="w-14 h-14 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-5">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 mb-3">Visual learning</h2>
                    <p class="text-slate-600">Charts, diagrams, and examples turn abstract formulas into ideas learners can actually picture.</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
                    <div class="w-14 h-14 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-5">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 mb-3">Always available practice</h2>
                    <p class="text-slate-600">Students can revisit resources, practice anytime, and keep momentum between lessons.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-slate-900 text-white py-16" style="background: #18a4a3">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold mb-4">Want the full curriculum?</h2>
            <p class="text-slate-300 mb-8">See how the learning journey fits together from basics to advanced problem solving.</p>
            <a href="curriculum.php" class="inline-flex px-6 py-3 rounded-lg bg-white text-brand-600 hover:bg-brand-700 font-semibold">Explore Curriculum</a>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
