<style>
    .carousel {
        min-height: 100vh;
        display: block;
        position: relative;
    }

    .progress-bar {
        position: absolute;
        top: 0;
        left: 0;
        height: 5px;
        width: 100%;
    }

    .progress-bar__fill {
        width: 0;
        height: inherit;
        background: #18a4a3;
        transition: all 0.16s;
    }

    .progress-bar--primary {
        z-index: 2;
    }

    .main-post-wrapper {
        position: absolute;
        width: 100%;
        height: 100%;
    }

    .slides {
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .main-post {
        position: absolute;
        top: 100%;
        right: 0;
        left: 0;
        bottom: 0;
        width: 100%;
        height: 100%;



    }

    .main-post__image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        bottom: 0;
    }

    .main-post__image img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .main-post__image::before {
        content: "";
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(#0e0d0e, 0.5);
    }


    .main-post__content {
        position: absolute;
        top: 40%;
        left: 4%;
        transform: translateY(-40%);
        color: #fff;
        width: 90%;
    }

    .main-post__tag-wrapper {
        margin: 0;
        display: inline-flex;
        overflow: hidden;
    }

    .main-post__tag {
        font-size: 0.95em;
        background: #18a4a3;
        padding: 6px 18px;
    }

    .main-post__title {
        font-weight: 700;
        font-size: 1.95em;
        line-height: 1.25;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    }

    .main-post__link {
        text-decoration: none;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        display: inline-flex;
        align-items: center;
    }


    .main-post__link-text {
        font-size: 0.9em;
    }

    .main-post__link-icon--arrow {
        margin-left: 12px;
    }

    .main-post__link-icon--play-btn {
        margin-right: 12px;
    }

    .main-post__link:hover .main-post__link-text,
    .main-post__link:hover .main-post__link-icon--arrow path {
        color: #18a4a3;
        stroke: #18a4a3;
    }

    .main-post--active {
        top: 0;
        z-index: 1;
        transition: top 0.9s 0.4s ease-out;
    }

    .main-post--not-active {
        top: 100%;
        z-index: 0;
        transition: top 0.75s 2s;
    }

    .main-post.main-post--active .main-post__tag-wrapper {
        transition: all 0.98s 1.9s;
    }

    .main-post.main-post--not-active .main-post__tag-wrapper {
        width: 0;
        transition: width 0.3s 0.2s;
    }

    .main-post.main-post--active .main-post__title {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 0.8s 1.42s, transform 0.5s 1.4s;
    }

    .main-post.main-post--not-active .main-post__title {
        transform: translateY(40px);
        opacity: 0;
        transition: transform 0.2s 0.35s, opacity 0.5s 0.2s;
    }

    .main-post.main-post--active .main-post__link {
        opacity: 1;
        transition: opacity 0.9s 2.2s;
    }

    .main-post.main-post--not-active .main-post__link {
        opacity: 0;
        transition: opacity 0.5s 0.2s;
    }

    .posts-wrapper {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        column-gap: 14px;
        position: absolute;
        bottom: 0;
        max-width: 95%;
        margin: auto;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        z-index: 1;
    }

    .post {
        background: rgba(14, 13, 14, 0.65);
        border-radius: 2rem;
        ;
        opacity: 0.3;
        color: #fff;
        position: relative;
        padding: 16px 20px;
        transition: opacity 0.2s linear;
    }

    .post__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.8em;
    }

    .post__tag {
        color: #80837e;
    }

    .post__title {
        font-weight: 400;
        font-size: 0.95em;
        line-height: 1.5;
    }

    .post--active {
        opacity: 1;
        background: rgba(#0e0d0e, 0.75);
        pointer-events: none;

    }

    .post--disabled {
        pointer-events: none;
    }

    .post:hover {
        cursor: pointer;
        opacity: 1;
    }

    .hide-on-mobile {
        display: none;
    }

    @media screen and (min-width: 768px) {
        .main-post__title {
            font-size: 2.9em;
        }
    }

    @media screen and (min-width: 1024px) {
        .hide-on-mobile {
            display: grid;
        }

        .hide-on-desktop {
            display: none;
        }
    }

    @media screen and (min-width: 1440px) {
        .main-post__content {
            width: 45%;
        }

        .posts-wrapper {
            left: 80%;
            transform: translatex(-80%);
            max-width: 70%;
        }
    }


    .show_bio_description {
        display: flex;
    }
</style>
<section>
    <div class="carousel">
        <div class="progress-bar progress-bar--primary hide-on-desktop">
            <div class="progress-bar__fill"></div>
        </div>

        <section class="main-post-wrapper">

            <div class="slides" id="application-hero-container-content">
                <article class="main-post main-post--active">
                    <div class="main-post__image">
                        <img src="assets/wall/d.jpeg" loading="lazy" />
                    </div>
                    <div class="main-post__content">
                        <div class="main-post__tag-wrapper">
                            <span class="main-post__tag">www.funmathsmastery.com</span>
                        </div>
                        <h1 style="color:white;" class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 mb-6 leading-tight">
                            Math doesn't have to be <span class="text-brand-600 relative whitespace-nowrap">
                                <span class="relative z-10">intimidating.</span>
                                <svg class="absolute bottom-0 left-0 w-full h-3 text-brand-100 -z-0" viewBox="0 0 100 10" preserveAspectRatio="none">
                                    <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="8" fill="none"></path>
                                </svg>
                            </span>
                        </h1>

                        <p style="color:white" class="mt-4 text-white text-lg sm:text-xl text-slate-600 mb-10 max-w-2xl mx-auto lg:mx-0">
                            Fun Maths Mastery turns complex concepts into clear, engaging, and highly visual lessons. Build your
                            confidence and conquer exams with our expert-led platform.
                        </p>

                        <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                            <a href="login.php" class="px-8 py-4 border border-transparent text-base font-bold rounded-lg text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-500/30 transition-all transform hover:-translate-y-1" style="width:fit-content;">
                                Start Learning Today
                            </a>
                            <a href="pricing.php" class="px-8 py-4 border border-slate-300 text-base font-bold rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-all" style="width:fit-content;">
                                View Plans
                            </a>
                        </div>
                    </div>
                </article>
                
                <article class="main-post">
                    <div class="main-post__image">
                        <img src="assets/wall/a.jpeg" loading="lazy" />
                    </div>
                    <div class="main-post__content">
                        <div class="main-post__tag-wrapper">
                            <span class="main-post__tag">www.funmathsmastery.com</span>
                        </div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 mb-6 leading-tight">
                            Math doesn't have to be <span class="text-brand-600 relative whitespace-nowrap">
                                <span class="relative z-10">intimidating.</span>
                                <svg class="absolute bottom-0 left-0 w-full h-3 text-brand-100 -z-0" viewBox="0 0 100 10" preserveAspectRatio="none">
                                    <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="8" fill="none"></path>
                                </svg>
                            </span>
                        </h1>

                        <p class="mt-4 text-lg sm:text-xl text-slate-600 mb-10 max-w-2xl mx-auto lg:mx-0">
                            Fun Maths Mastery turns complex concepts into clear, engaging, and highly visual lessons. Build your
                            confidence and conquer exams with our expert-led platform.
                        </p>

                        <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                            <a href="login.php" class="px-8 py-4 border border-transparent text-base font-bold rounded-lg text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-500/30 transition-all transform hover:-translate-y-1" style="width:fit-content;">
                                Start Learning Today
                            </a>
                            <a href="pricing.php" class="px-8 py-4 border border-slate-300 text-base font-bold rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-all" style="width:fit-content;">
                                View Plans
                            </a>
                        </div>
                    </div>
                </article>

                <article class="main-post ">
                    <div class="main-post__image">
                        <img src="https://images.unsplash.com/photo-1570616969692-54d6ba3d0397?q=80&w=1122&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" loading="lazy" />
                    </div>
                    <div class="main-post__content">
                        <div class="main-post__tag-wrapper">
                            <span class="main-post__tag">www.funmathsmastery.com</span>
                        </div>
                        <h1 style="color: white;" class="text-4xl text-white sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 mb-6 leading-tight">
                            Math doesn't have to be <span class="text-brand-600 relative whitespace-nowrap">
                                <span class="relative z-10">intimidating.</span>
                                <svg class="absolute bottom-0 left-0 w-full h-3 text-brand-100 -z-0" viewBox="0 0 100 10" preserveAspectRatio="none">
                                    <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="8" fill="none"></path>
                                </svg>
                            </span>
                        </h1>

                        <p class="mt-4 text-white text-lg sm:text-xl text-slate-600 mb-10 max-w-2xl mx-auto lg:mx-0">
                            Fun Maths Mastery turns complex concepts into clear, engaging, and highly visual lessons. Build your
                            confidence and conquer exams with our expert-led platform.
                        </p>

                        <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                            <a href="login.php" class="px-8 py-4 border border-transparent text-base font-bold rounded-lg text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-500/30 transition-all transform hover:-translate-y-1" style="width:fit-content;">
                                Start Learning Today
                            </a>
                            <a href="pricing.php" class="px-8 py-4 border border-slate-300 text-base font-bold rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-all" style="width:fit-content;">
                                View Plans
                            </a>
                        </div>
                    </div>
                </article>



                <article class="main-post ">
                    <div class="main-post__image">
                        <img src="https://plus.unsplash.com/premium_photo-1722859325649-c75b975934c1?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3Dv" loading="lazy" />
                    </div>
                    <div class="main-post__content">
                        <div class="main-post__tag-wrapper">
                            <span class="main-post__tag">www.funmathsmastery.com</span>
                        </div>
                        <h1 class="text-4xl text-white sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 mb-6 leading-tight">
                            Math doesn't have to be <span class="text-brand-600 relative whitespace-nowrap">
                                <span class="relative z-10">intimidating.</span>
                                <svg class="absolute bottom-0 left-0 w-full h-3 text-brand-100 -z-0" viewBox="0 0 100 10" preserveAspectRatio="none">
                                    <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="8" fill="none"></path>
                                </svg>
                            </span>
                        </h1>

                        <p class="mt-4 text-white text-lg sm:text-xl text-slate-600 mb-10 max-w-2xl mx-auto lg:mx-0">
                            Fun Maths Mastery turns complex concepts into clear, engaging, and highly visual lessons. Build your
                            confidence and conquer exams with our expert-led platform.
                        </p>

                        <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                            <a href="login.php" class="px-8 py-4 border border-transparent text-base font-bold rounded-lg text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-500/30 transition-all transform hover:-translate-y-1" style="width:fit-content;">
                                Start Learning Today
                            </a>
                            <a href="pricing.php" class="px-8 py-4 border border-slate-300 text-base font-bold rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-all" style="width:fit-content;">
                                View Plans
                            </a>
                        </div>
                    </div>
                </article>

            </div>
        </section>

        <div id="application-hero-subtitles-container-content" style="display: none; padding-bottom: 1rem;" class="posts-wrapper">
            <article class="post post--active">
                <div class="progress-bar">
                    <div class="progress-bar__fill"></div>
                </div>
                <section class="post__header">
                    <span class="post__tag">Personalised For You</span>
                    <p class="post__published"></p>
                </section>
                <h2 class="post__title">Providing Access to users.</h2>
            </article>

            <article class="post ">
                <div class="progress-bar">
                    <div class="progress-bar__fill"></div>
                </div>
                <section class="post__header">
                    <span class="post__tag">Personalised For You</span>
                    <p class="post__published"></p>
                </section>
                <h2 class="post__title">Providing Access to users.</h2>
            </article>

            <article class="post ">
                <div class="progress-bar">
                    <div class="progress-bar__fill"></div>
                </div>
                <section class="post__header">
                    <span class="post__tag">Personalised For You</span>
                    <p class="post__published"></p>
                </section>
                <h2 class="post__title">Providing Access to users.</h2>
            </article>

            <article class="post ">
                <div class="progress-bar">
                    <div class="progress-bar__fill"></div>
                </div>
                <section class="post__header">
                    <span class="post__tag">Personalised For You</span>
                    <p class="post__published"></p>
                </section>
                <h2 class="post__title">Providing Access to users.</h2>
            </article>


        </div>
    </div>
</section>



<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script>
    $(document).ready(function() {
        var owl = $(".owl-carousel");
        owl.owlCarousel({
            loop: true,
            margin: 10,
            nav: true,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 2
                },
                1000: {
                    items: 4
                }
            }
        });
        owl.on(`initialized.owl.carousel`, function(event) {
            var carouselContainer = $(event.target).closest(`.owl-carousel`);
            carouselContainer.append(`<div class="owl-controls"></div>`);
            carouselContainer.find(`.owl-prev`).appendTo(carouselContainer.find(`.owl-controls`));
            carouselContainer.find(`.owl-next`).appendTo(carouselContainer.find(`.owl-controls`));
        });
    });


    function execute_courosel() {
        const mainProgressBar = document.querySelector(
            ".progress-bar--primary .progress-bar__fill"
        );
        const mainPosts = document.querySelectorAll(".main-post");
        const posts = document.querySelectorAll(".post");

        let i = 0;
        let postIndex = 0;
        let currentPost = posts[postIndex];
        let currentMainPost = mainPosts[postIndex];

        let progressInterval = setInterval(progress, 100);

        function progress() {
            if (i === 100) {
                i = -5;
                // reset progress bar
                currentPost.querySelector(".progress-bar__fill").style.width = 0;
                mainProgressBar.style.width = 0;

                currentMainPost.classList.remove("hide_component");

                currentPost.classList.remove("post--active");
                currentPost.classList.add('hide_component');

                postIndex++;
                currentMainPost.classList.add("main-post--not-active");
                currentMainPost.classList.remove("main-post--active");

                // reset postIndex to loop over the slides again
                if (postIndex === posts.length) {
                    postIndex = 0;
                }

                currentPost = posts[postIndex];
                currentMainPost = mainPosts[postIndex];
            } else {
                i++;
                currentPost.querySelector(".progress-bar__fill").style.width = `${i}%`;
                mainProgressBar.style.width = `${i}%`;
                currentPost.classList.add("post--active");
                currentPost.classList.remove('hide_component');

                currentMainPost.classList.add("main-post--active");
                currentMainPost.classList.remove("main-post--not-active");
            }
        }

        posts.forEach((post, index) => {
            post.addEventListener("click", () => {
                disablePostsTemporarily();
                i = 0; // Reset the progress bar
                postIndex = index;
                updatePosts();
            });
        });

        function disablePostsTemporarily() {
            // Disable pointer events on all posts
            posts.forEach((post) => {
                post.classList.add("post--disabled");
            });

            // Re-enable pointer events after 2 1/2 seconds
            setTimeout(() => {
                posts.forEach((post) => {
                    post.classList.remove("post--disabled");
                });
            }, 2500);
        }

        function updatePosts() {
            // Reset all progress bars and classes
            posts.forEach((post) => {
                post.querySelector(".progress-bar__fill").style.width = 0;
                post.classList.remove("post--active");
            });

            mainPosts.forEach((mainPost) => {
                mainPost.classList.add("main-post--not-active");
                mainPost.classList.remove("main-post--active");
            });

            // Update the current post and main post
            currentPost = posts[postIndex];
            currentMainPost = mainPosts[postIndex];

            currentPost.querySelector(".progress-bar__fill").style.width = `${i}%`;
            mainProgressBar.style.width = `${i}%`;
            currentPost.classList.add("post--active");

            currentMainPost.classList.add("main-post--active");
            currentMainPost.classList.remove("main-post--not-active");
        }
    }

    execute_courosel();
</script>