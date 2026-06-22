<?php
require_once 'config/functions.php';

renderPublicLayoutStart(
    'Testimonials',
    'Read student and parent feedback about the learning experience.',
    'testimonials',
    '/testimonials.php'
);
?>

<style>
    /* Container styling */
    .review-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    font-family: sans-serif;
    color: #fff;
    }

    /* Review text clamped state */
    .review-text {
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 6; /* Change this to show more/fewer lines initially */
    -webkit-box-orient: vertical;  
    overflow: hidden;
    transition: all 0.3s ease-in-out;
    }

    /* Expanded state */
    .review-text.expanded {
    -webkit-line-clamp: unset;
    }

    /* The toggle button styling */
    .read-more-btn {
    background: none;
    border: none;
    color: #3b82f6; /* Modern accent blue */
    cursor: pointer;
    font-weight: 600;
    padding: 0;
    margin-top: 0.5rem;
    font-size: 0.9rem;
    transition: color 0.2s;
    }

    .read-more-btn:hover {
    color: #60a5fa;
    text-decoration: underline;
    }
</style>

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
                        <p class="text-slate-600 review-text mb-4">"I'm a parent of Tshwarelo Melato ,the first student in FUN MATH MASTERY .Tshwarelo joined Math Mastery in 2024 ,2nd term ,he was baddling alot with Math .
                            <br><br>
                            We only had just one tutor that started with FUN MATH MASTERY wich is Mr Nosipho .Our journey form 2024 to 2026 together was nice because we worked together  ❤️.Mrs Nosipho was not sure about continuing with FUN MATH MASTERY, she became more discouraged  because she only had one student for a long period of time. 
                            <br><br>
                            As a parent I promised her that we need to pray and ask GOD to intervene in this journey. Today I am very proud  of her because FUN MATH MASTERY is succeeding .Now we have more students and more tutors .Even today she always reminds me that she appreciates me for the journey that we went through together and the prayers that I mentioned her in and now GOD has answered the prayers🙌 Thank you❤️"</p>
                        
                        <img style="width: 75%; object-fit: cover; height: 15rem; border-radius: 1rem; object-position: top; display: block; margin: 1rem auto;" class="h-48 flex items-center justify-center text-white text-6xl" src="assets/review1.jpeg">

                        <div class="font-semibold text-slate-900">Tonto Melato from Gauteng (parent)</div>
                    </div>
                </div>

                <div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="flex items-center gap-1 text-yellow-400 mb-3">★★★★★</div>
                        <p class="text-slate-600 review-text mb-4">"My name is Tshwarelo Ignatius Melato, and i am a grade 11 learner from Gauteng (Kagiso). I am proud to say i was the very first student of Fun Maths Mastery. I started attending Fun Maths in grade 9, and since then I have been excelling in Mathematics. The classes have helped me to build a strong foundation,  improve my problem solving skills and gain confidence in my abilities. When I joined there was only one tutor,  Mrs Nosy. At that time, she did not believe that Fun Maths Mastery would grow into what it is today with so many learners joining from  different places.  It has been inspiring to watch the program grow  from the very beginning.  Mrs Nosy has always dedicated, patient and passionate about teaching. She explains difficult topics in a way that is easy to understand and always encourages learners to work hard and believe in themselves. Her support has played a big role in my success in Mathematics. As Fun Maths Mastery continued to grow,  Mrs Nosy aften thanked my grandma for the success and growth of the programme. It is wonderful to see those prayers have been answered, as more and more learners are now benefiting from the classes. I have also enjoyed being part of the online classes and learning with other students. The environment is friendly, supportive and motivating. What i love most about Fun Maths Mastery is that it makes Mathematics enjoyable and helps learners to reach their full potential. I'm grateful to Mrs Nosy and the Fun Maths Mastery for their hard work and dedication. It has been an honor to be part of this journey 🙏 from the start, and I look forward to seeing Fun Maths Mastery continue to grow and help many more learners achieve success in Mathematics. THANK YOU."</p>
                        
                        <img style="width: 75%; object-fit: cover; height: 15rem; border-radius: 1rem; object-position: top; display: block; margin: 1rem auto;" class="h-48 flex items-center justify-center text-white text-6xl" src="assets/review2.jpeg">

                        <div class="font-semibold text-slate-900">Tshwarelo Ignatius Melato Grade 11</div>
                    </div>
                </div>
                <div></div>
                <div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="flex items-center gap-1 text-yellow-400 mb-3">★★★★★</div>
                        <p class="text-slate-600 review-text mb-4">"When I joined Fun Maths Mastery, I was nervous because math always stressed me out. But the classes with FMM tutor Miss Kay changed that completely. She explains every topic step by step and never makes you feel dumb for asking questions. <br><br>What I love most is being with my online classmates. Even though we’re not in the same room, we motivate each other. When someone gets a sum right, we all celebrate. When someone’s stuck, Miss Kay or another classmate helps out. It feels like we’re learning together, not alone.<br><br>Being in these classes taught me that practice really does beat panic. I went from dreading math to actually looking forward to it. Fun Maths Mastery tutor made math make sense, and my classmates made it fun. I’ve learned that if I don’t give up, I can understand anything."_</p>
                        <div class="font-semibold text-slate-900">Naledi Lumkwana, Gauteng, Grade 9</div>
                    </div>
                </div>

                <div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="flex items-center gap-1 text-yellow-400 mb-3">★★★★★</div>
                        <p class="text-slate-600 review-text mb-4">I joined Fun Math Mastery when math was my most stressful subject, but the way this group teaches changed how I think about numbers completely. Instead of just memorizing steps, the tutors break every topic down so it actually makes sense, and we practice together until it clicks. The environment is supportive - no one makes you feel dumb for asking questions. Since joining 3 months ago, I’ve noticed my understanding is deeper, my test anxiety is lower, and I can tackle problems I used to skip. For me, Fun Math Mastery didn’t just improve my scores, it rebuilt my confidence in math.

                        <div class="font-semibold text-slate-900">Kamogelo kgarane, Grade 9</div>
                    </div>
                </div>

                <div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="flex items-center gap-1 text-yellow-400 mb-3">★★★★★</div>
                        <p class="text-slate-600 review-text mb-4">“I joined this online Grade 11 Fun Maths class about a month ago, and it has made a big difference in my Maths journey. Before joining the class, my marks were very low, and I was struggling with many topics. I started with a mark of 33%, but after attending the classes, practising, and getting the right guidance, my mark improved to 85%.<br><br>This class has helped me understand Maths better, become more confident, and enjoy learning. I am grateful for the support and the way the lessons are explained because they have truly helped me improve.””</p>
                        <div class="font-semibold text-slate-900"> Bahle Majija, Grade 11</div>
                    </div>
                </div>

                <div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <div class="flex items-center gap-1 text-yellow-400 mb-3">★★★★★</div>
                        <p class="text-slate-600 review-text mb-4">“My Fun Math Mastery Journey:<br>
                            <br>My name is Tshepang Melato, a Grade 8 learner, and I’ve been part of Fun Math Mastery since January. When I first started, math felt really difficult and I wasn’t confident in class. But the tutors at Fun Math Mastery changed that for me. 
                            <br>They never rush us. They take the time to explain step by step and make sure we all truly understand how things work before moving on. What I love most is that learning with them doesn’t feel stressful. We laugh together, help each other, and actually enjoy solving problems as a group. 
                            <br>Because of Fun Math Mastery, math isn’t something I dread anymore. I look forward to my classes now, and I’m proud of how much I’ve improved. I’m so grateful to be in a class where we learn, laugh, and grow together.

                            <br>Thank you”
                        </p>
                        <div class="font-semibold text-slate-900"> Tshepang Melato, Grade 8 </div>
                    </div>
                </div>



                
            </div>
        </div>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
        const reviews = document.querySelectorAll(".review-text");

        reviews.forEach((review) => {
            // Check if the actual text height is greater than the visible clamped height
            if (review.scrollHeight > review.clientHeight) {
            // Create the button dynamically
            const btn = document.createElement("button");
            btn.className = "read-more-btn";
            btn.innerText = "Read More";

            // Insert the button right after the review text
            review.parentNode.insertBefore(btn, review.nextSibling);

            // Toggle functionality
            btn.addEventListener("click", () => {
                const isExpanded = review.classList.toggle("expanded");
                btn.innerText = isExpanded ? "Read Less" : "Read More";
            });
            }
        });
        });
    </script>
<?php
renderPublicLayoutEnd();
