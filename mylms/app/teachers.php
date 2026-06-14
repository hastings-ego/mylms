<?php
require_once 'config/functions.php';

renderPublicLayoutStart(
    'Teachers',
    'Meet the math educators who guide lessons, explain concepts, and support learners.',
    'teachers',
    '/teachers.php'
);
?>
    <section class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-12">
                <p class="text-brand-600 font-semibold uppercase tracking-wide text-sm mb-3">Meet your mentors</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight">Expert teachers who make hard topics feel simple.</h1>
                <p class="mt-5 text-lg text-slate-600">Our teaching style is warm, structured, and focused on helping students build real understanding.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
                    <div class="h-48 bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-6xl">👩‍🏫</div>
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-slate-900">Dr. Emily Rodriguez</h2>
                        <p class="text-brand-600 text-sm font-semibold mt-1 mb-3">Head of Mathematics, PhD</p>
                        <p class="text-slate-600">Specializes in calculus and trigonometry with a focus on intuitive explanations.</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
                    <div class="h-48 bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white text-6xl">👨‍🏫</div>
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-slate-900">Michael Chen</h2>
                        <p class="text-brand-600 text-sm font-semibold mt-1 mb-3">Algebra & Geometry Lead</p>
                        <p class="text-slate-600">Known for visual teaching and making foundational concepts click fast.</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
                    <div class="h-48 bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-6xl">👩‍🏫</div>
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-slate-900">Dr. Lisa Thompson</h2>
                        <p class="text-brand-600 text-sm font-semibold mt-1 mb-3">Pre-Calculus Specialist</p>
                        <p class="text-slate-600">Creates adaptive problem sets that build confidence step by step.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
