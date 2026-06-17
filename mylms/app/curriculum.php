<?php
require_once 'config/functions.php';

renderPublicLayoutStart(
    'Curriculum',
    'Browse the core math subjects covered by Fun Maths Mastery, from algebra through calculus.',
    'curriculum',
    '/curriculum.php'
);
?>
    <section class="bg-slate-900 text-white" style="background: #18a4a3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-24">
            <div class="max-w-3xl">
                <p class="text-brand-400 font-semibold uppercase tracking-wide text-sm mb-3">Complete progression</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight">A curriculum that grows with the learner.</h1>
                <p class="mt-6 text-lg text-slate-300">We move from fundamentals to advanced problem solving in a structured way, so students always know what comes next.</p>
            </div>
        </div>
    </section>

    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-brand-600 mb-2">Algebra</h2>
                    <p class="text-slate-600 text-sm">Equations, inequalities, polynomials, and quadratics.</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-brand-600 mb-2">Geometry</h2>
                    <p class="text-slate-600 text-sm">Proofs, theorems, trigonometry, and spatial reasoning.</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-brand-600 mb-2">Pre-Calculus</h2>
                    <p class="text-slate-600 text-sm">Functions, limits, sequences, and advanced trigonometry.</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-brand-600 mb-2">Calculus</h2>
                    <p class="text-slate-600 text-sm">Derivatives, integrals, and real-world applications.</p>
                </div>
            </div>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
