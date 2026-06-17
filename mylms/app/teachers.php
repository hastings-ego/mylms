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
                    <div class="p-6">
                        <img style="width: 100%; object-fit: cover; height: 21rem; border-radius: 2rem;" class="h-48 flex items-center justify-center text-white text-6xl" src="assets/tutors/nosipho.jpeg" alt="Nosipho Hermanus">
                        <h2 class="text-xl font-bold text-slate-900">Nosipho Hermanus </h2>
                        <p class="text-brand-600 text-sm font-semibold mt-1 mb-3">Founder of Fun Maths Mastery </p>
                        <p class="text-slate-600">Bachelor’s Degree in Marketing and psychology. <br>5 years of experience tutoring students of all Grades. History of taking a student from 30% to 70%</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
                    <div class="p-6">
                        <img style="width: 100%; object-fit: cover; height: 21rem; border-radius: 2rem;" class="h-48 flex items-center justify-center text-white text-6xl" src="assets/tutors/miss.jpeg" alt="Miss Keyolen Gouws">
                        <h2 class="text-xl font-bold text-slate-900">Miss Keyolen Gouws </h2>
                        <p class="text-brand-600 text-sm font-semibold mt-1 mb-3">Tutors of Fun Maths Mastery </p>
                        <p class="text-slate-600">Currently studying teaching: bachelor of Education in the FET phase. Did a entrepreneurship course, Did a computer course, Has Tefl certificate</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
                    <div class="p-6">
                        <img style="width: 100%; object-fit: cover; height: 21rem; border-radius: 2rem;" class="h-48 flex items-center justify-center text-white text-6xl" src="assets/tutors/Jonathon.jpeg" alt="Mr Jonathon Mnyandu">
                        <h2 class="text-xl font-bold text-slate-900">Mr Jonathon Mnyandu </h2>
                        <p class="text-brand-600 text-sm font-semibold mt-1 mb-3">Tutors of Fun Maths Mastery </p>
                        <p class="text-slate-600">Specialised mathematical and physical sciences tutor sciences tutor, having worked with 27 learners over 6 years. Students marks tend to improve over 6 weeks</p>
                    </div>
                </div>


            </div>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
