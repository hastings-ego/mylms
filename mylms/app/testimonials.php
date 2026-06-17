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
                <div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="flex items-center gap-1 text-yellow-400 mb-3">★★★★★</div>
                        <p class="text-slate-600 mb-4">"When I joined Fun Maths Mastery, I was nervous because math always stressed me out. But the classes with FMM tutor Miss Kay changed that completely. She explains every topic step by step and never makes you feel dumb for asking questions. <br><br>What I love most is being with my online classmates. Even though we’re not in the same room, we motivate each other. When someone gets a sum right, we all celebrate. When someone’s stuck, Miss Kay or another classmate helps out. It feels like we’re learning together, not alone.<br><br>Being in these classes taught me that practice really does beat panic. I went from dreading math to actually looking forward to it. Fun Maths Mastery tutor made math make sense, and my classmates made it fun. I’ve learned that if I don’t give up, I can understand anything."_</p>
                        <div class="font-semibold text-slate-900">Naledi Lumkwana, Gauteng, Grade 9</div>
                    </div>
                </div>

                <div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="flex items-center gap-1 text-yellow-400 mb-3">★★★★★</div>
                        <p class="text-slate-600 mb-4">I joined Fun Math Mastery when math was my most stressful subject, but the way this group teaches changed how I think about numbers completely. Instead of just memorizing steps, the tutors break every topic down so it actually makes sense, and we practice together until it clicks. The environment is supportive - no one makes you feel dumb for asking questions. Since joining 3 months ago, I’ve noticed my understanding is deeper, my test anxiety is lower, and I can tackle problems I used to skip. For me, Fun Math Mastery didn’t just improve my scores, it rebuilt my confidence in math.

                        <div class="font-semibold text-slate-900">Kamogelo kgarane, Grade 9</div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex items-center gap-1 text-yellow-400 mb-3">★★★★★</div>
                    <p class="text-slate-600 mb-4">“My daughter is excited about math now. The teachers are responsive and the structure is easy to follow.”</p>
                    <div class="font-semibold text-slate-900">Hardy Hastings, Parent</div>
                </div>
            </div>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
