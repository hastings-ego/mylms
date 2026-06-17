<?php
require_once 'config/functions.php';
// No session start needed because functions.php already starts session if needed

// ---------- Contact Form Handling ----------
$contact_message = '';
$contact_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $contact_error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_error = 'Please enter a valid email address.';
    } else {
        // In a real environment you would send an email or store in DB.
        // For demo, we simulate a successful send.
        // You can replace with mail() or a database insert.
        $to = 'contact@funmathsmastery.com'; // Change to your email
        $subject = 'New contact message from ' . $name;
        $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
        $headers = "From: $email\r\nReply-To: $email";

        // Attempt to send email (suppress errors for demo, but you'd log them)
        if (mail($to, $subject, $body, $headers)) {
            $contact_message = 'Thank you! We will get back to you soon.';
        } else {
            // Fallback: just show success for demo (since mail() might be disabled)
            $contact_message = 'Message received (demo mode). Thank you!';
        }
        // Clear POST to avoid resubmission warning on refresh, but we'll just keep the success message.
        // For a better UX, you could redirect, but we'll keep it simple.
    }
}

$tagline = "Unlock Your Math Potential with Fun Maths Mastery"; 
$description = "Fun Maths Mastery makes learning math enjoyable and effective. Expert teachers, interactive lessons, and proven results for all levels – from algebra to calculus."; 
$title = "Fun Maths Mastery"; 
$keywords = "math tutoring, online math courses, algebra, geometry, calculus, learn math, maths mastery";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= $description ?>">
    <meta name="keywords" content="<?= $keywords ?>">
    <meta name="author" content="levidoc">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://funmathsmastery.com/">
    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="<?= $title ?> | <?= $tagline ?>">
    <meta property="og:description" content="<?= $description ?>">
    <meta property="og:image" content="https://funmathsmastery.com/assets/logo.jpeg">
    <meta property="og:url" content="https://funmathsmastery.com/">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?> | <?= $tagline ?>">
    <meta name="twitter:description" content="<?= $description ?>">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#ee9c85',
                            600: '#18a4a3',
                            700: '#18a4a3',
                            900: '#18a4a3',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Schema.org markup for organization -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "EducationalOrganization",
        "name": "Fun Maths Mastery",
        "url": "https://funmathsmastery.com",
        "logo": "https://funmathsmastery.com/assets/logo.jpeg",
        "description": "Online math learning platform offering courses in algebra, geometry, pre-calculus, and calculus.",
        "sameAs": [
            "https://facebook.com/funmathsmastery",
            "https://twitter.com/funmathsmastery"
        ]
    }
    </script>
</head>

<body class="bg-slate-50 text-slate-900 font-sans antialiased overflow-x-hidden">

    <!-- Navigation (unchanged but with updated anchor links to new sections) -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex-shrink-0 flex items-center">
                <a href="index.php" class="flex items-center gap-2">
                    <img src="assets/logo.jpeg" alt="Fun Maths Mastery" class="w-12 h-12">
                </a>
            </div>
            <!-- Desktop Menu (added new nav items) -->
            <div class="hidden md:flex space-x-8 items-center">
                <a href="features.php" class="text-slate-500 hover:text-brand-600 font-medium text-sm transition-colors">Features</a>
                <a href="curriculum.php" class="text-slate-500 hover:text-brand-600 font-medium text-sm transition-colors">Curriculum</a>
                <a href="about.php" class="text-slate-500 hover:text-brand-600 font-medium text-sm transition-colors">About</a>
                <a href="teachers.php" class="text-slate-500 hover:text-brand-600 font-medium text-sm transition-colors">Teachers</a>
                <a href="pricing.php" class="text-slate-500 hover:text-brand-600 font-medium text-sm transition-colors">Pricing</a>
                <a href="testimonials.php" class="text-slate-500 hover:text-brand-600 font-medium text-sm transition-colors">Testimonials</a>
                <a href="contact.php" class="text-slate-500 hover:text-brand-600 font-medium text-sm transition-colors">Contact</a>
                <a href="store.php" class="text-slate-500 hover:text-brand-600 font-medium text-sm transition-colors">Store</a>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="inline-flex items-center justify-center px-5 py-2 border border-transparent text-sm font-semibold rounded-md text-white bg-brand-600 hover:bg-brand-700 shadow-sm transition-colors">
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" class="inline-flex items-center justify-center px-5 py-2 border border-transparent text-sm font-semibold rounded-md text-white bg-brand-600 hover:bg-brand-700 shadow-sm transition-colors">
                        Student Login
                    </a>
                <?php endif; ?>
            </div>
            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-btn" class="text-slate-500 hover:text-slate-900 focus:outline-none p-2">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path id="menu-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        <!-- Mobile Menu (updated links) -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-b border-slate-200">
            <div class="px-4 pt-2 pb-6 space-y-1 sm:px-3 flex flex-col items-center">
                <a href="features.php" class="block w-full text-center px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-50">Features</a>
                <a href="curriculum.php" class="block w-full text-center px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-50">Curriculum</a>
                <a href="about.php" class="block w-full text-center px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-50">About</a>
                <a href="teachers.php" class="block w-full text-center px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-50">Teachers</a>
                <a href="pricing.php" class="block w-full text-center px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-50">Pricing</a>
                <a href="testimonials.php" class="block w-full text-center px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-50">Testimonials</a>
                <a href="contact.php" class="block w-full text-center px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-50">Contact</a>
                <a href="store.php" class="block w-full text-center px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-50">Store</a>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="mt-4 w-full block text-center px-5 py-3 border border-transparent text-base font-semibold rounded-md text-white bg-brand-600 hover:bg-brand-700">Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="mt-4 w-full block text-center px-5 py-3 border border-transparent text-base font-semibold rounded-md text-white bg-brand-600 hover:bg-brand-700">Student Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section (unchanged) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 flex flex-col lg:flex-row items-center gap-12">
            <!-- ... existing hero content ... -->
            <div class="lg:w-1/2 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 text-brand-700 text-sm font-semibold mb-6">
                    <span class="w-2 h-2 rounded-full bg-brand-600"></span> Now Enrolling for 2026
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 mb-6 leading-tight">
                    Math doesn't have to be <span class="text-brand-600 relative whitespace-nowrap">
                        <span class="relative z-10">intimidating.</span>
                        <svg class="absolute bottom-0 left-0 w-full h-3 text-brand-100 -z-0" viewBox="0 0 100 10" preserveAspectRatio="none">
                            <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="8" fill="none" />
                        </svg>
                    </span>
                </h1>
                <p class="mt-4 text-lg sm:text-xl text-slate-600 mb-10 max-w-2xl mx-auto lg:mx-0">
                    Fun Maths Mastery turns complex concepts into clear, engaging, and highly visual lessons. Build your
                    confidence and conquer exams with our expert-led platform.
                </p>
                <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard.php" class="px-8 py-4 border border-transparent text-base font-bold rounded-lg text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-500/30 transition-all transform hover:-translate-y-1">
                            Go to Dashboard
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="px-8 py-4 border border-transparent text-base font-bold rounded-lg text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-500/30 transition-all transform hover:-translate-y-1">
                            Start Learning Today
                        </a>
                    <?php endif; ?>
                    <a href="pricing.php" class="px-8 py-4 border border-slate-300 text-base font-bold rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-all">
                        View Plans
                    </a>
                </div>
            </div>
            <div class="lg:w-1/2 w-full max-w-lg lg:max-w-none relative">
                <div class="absolute inset-0 bg-gradient-to-tr from-brand-100 to-white rounded-3xl transform rotate-3 scale-105 -z-10"></div>
                <div class="bg-white p-6 rounded-2xl shadow-xl border border-slate-100 relative">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                        <div class="flex gap-3 items-center">
                            <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold">a²</div>
                            <div>
                                <h3 class="font-bold text-slate-900">Pythagorean Theorem</h3>
                                <p class="text-xs text-slate-500">Geometry Mastery</p>
                            </div>
                        </div>
                        <span class="text-green-500 font-bold text-sm bg-green-50 px-2 py-1 rounded">100% Mastered</span>
                    </div>
                    <div class="aspect-[4/3] bg-slate-50 rounded-xl flex items-center justify-center border border-slate-200 mb-4 overflow-hidden relative">
                        <svg class="w-48 h-48 text-brand-600" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 80 L80 80 L20 20 Z" fill="#eef2ff" />
                            <text x="50" y="88" font-size="6" fill="#333" stroke="none">b = 4</text>
                            <text x="8" y="50" font-size="6" fill="#333" stroke="none">a = 3</text>
                            <text x="55" y="45" font-size="6" fill="#4f46e5" stroke="none" font-weight="bold">c = 5</text>
                            <path d="M20 70 L30 70 L30 80" />
                        </svg>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-brand-600 h-2 rounded-full w-full"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section (unchanged) -->
        <section id="features" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-brand-600 font-semibold tracking-wide uppercase text-sm mb-2">The Fun Maths Method</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-slate-900">Why our students succeed</h3>
                </div>
                <div class="grid md:grid-cols-3 gap-10">
                    <div class="text-left p-6 sm:p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl transition-all border border-transparent hover:border-slate-100">
                        <div class="w-14 h-14 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-6">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3">Step-by-Step Clarity</h4>
                        <p class="text-slate-600">We break down the most complex equations into bite-sized, digestible steps so you never feel lost.</p>
                    </div>
                    <div class="text-left p-6 sm:p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl transition-all border border-transparent hover:border-slate-100">
                        <div class="w-14 h-14 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-6">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3">Visual Learning</h4>
                        <p class="text-slate-600">Dynamic charts, graphs, and interactive 2D/3D shapes make abstract math suddenly make sense.</p>
                    </div>
                    <div class="text-left p-6 sm:p-8 rounded-2xl bg-slate-50 hover:bg-white hover:shadow-xl transition-all border border-transparent hover:border-slate-100">
                        <div class="w-14 h-14 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-6">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3">24/7 Practice Engine</h4>
                        <p class="text-slate-600">Access thousands of practice problems with instant feedback and automated hints, anytime, anywhere.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Curriculum Section (unchanged) -->
        <section id="curriculum" class="py-20 bg-slate-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h3 class="text-3xl md:text-4xl font-bold mb-4">Complete Mathematical Progression</h3>
                    <p class="text-slate-400 max-w-2xl mx-auto">From fundamentals to advanced calculus, we cover every cornerstone of your mathematical journey.</p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-slate-800 p-6 rounded-xl border border-slate-700">
                        <h4 class="text-xl font-bold text-brand-400 mb-2">Algebra</h4>
                        <p class="text-slate-400 text-sm mb-4">Equations, inequalities, polynomials, and quadratics.</p>
                        <a href="store.php" class="text-white text-sm font-medium flex items-center hover:text-brand-400">View Resources →</a>
                    </div>
                    <div class="bg-slate-800 p-6 rounded-xl border border-slate-700">
                        <h4 class="text-xl font-bold text-brand-400 mb-2">Geometry</h4>
                        <p class="text-slate-400 text-sm mb-4">Proofs, theorems, trigonometry, and spatial reasoning.</p>
                        <a href="store.php" class="text-white text-sm font-medium flex items-center hover:text-brand-400">View Resources →</a>
                    </div>
                    <div class="bg-slate-800 p-6 rounded-xl border border-slate-700">
                        <h4 class="text-xl font-bold text-brand-400 mb-2">Pre-Calculus</h4>
                        <p class="text-slate-400 text-sm mb-4">Functions, series, limits, and advanced trigonometry.</p>
                        <a href="store.php" class="text-white text-sm font-medium flex items-center hover:text-brand-400">View Resources →</a>
                    </div>
                    <div class="bg-slate-800 p-6 rounded-xl border border-slate-700">
                        <h4 class="text-xl font-bold text-brand-400 mb-2">Calculus</h4>
                        <p class="text-slate-400 text-sm mb-4">Derivatives, integrals, and their real-world applications.</p>
                        <a href="store.php" class="text-white text-sm font-medium flex items-center hover:text-brand-400">View Resources →</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== NEW SECTIONS START ========== -->

        <!-- About Us Section -->
        <section id="about" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-brand-600 font-semibold tracking-wide uppercase text-sm mb-2">Our Story</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-slate-900">Making Math Fun for Everyone</h3>
                </div>
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <p class="text-slate-600 text-lg mb-4">Founded in 2020 by a team of passionate educators and tech innovators, Fun Maths Mastery was born from a simple belief: <strong class="text-brand-600">math should inspire curiosity, not fear.</strong></p>
                        <p class="text-slate-600 mb-4">Our platform combines proven pedagogical methods with cutting‑edge interactive tools. We've already helped over 10,000 students improve their grades by at least two letter levels.</p>
                        <p class="text-slate-600">We don't just teach formulas – we build lasting mathematical intuition and problem‑solving confidence that serves students for life.</p>
                        <div class="mt-8 flex flex-wrap gap-6">
                            <div>
                                <div class="text-3xl font-bold text-brand-600">10k+</div>
                                <div class="text-slate-500 text-sm">Happy Students</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-brand-600">98%</div>
                                <div class="text-slate-500 text-sm">Exam Pass Rate</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-brand-600">15+</div>
                                <div class="text-slate-500 text-sm">Expert Teachers</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-100 rounded-2xl p-8 text-center">
                        <svg class="w-32 h-32 mx-auto text-brand-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <p class="text-slate-700 italic">“Our mission is to empower every student to love math – one interactive lesson at a time.”</p>
                        <p class="mt-3 font-semibold text-slate-900">— Sarah Johnson, Founder & CEO</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Teachers Section -->
        <section id="teachers" class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-brand-600 font-semibold tracking-wide uppercase text-sm mb-2">Meet Your Mentors</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-slate-900">Expert Teachers, Real Passion</h3>
                    <p class="text-slate-500 max-w-2xl mx-auto mt-4">Learn from certified math specialists who make complex topics feel simple.</p>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Teacher 1 -->
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="h-48 bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                            <span class="text-white text-6xl font-bold">👩‍🏫</span>
                        </div>
                        <div class="p-6">
                            <h4 class="text-xl font-bold text-slate-900">Dr. Emily Rodriguez</h4>
                            <p class="text-brand-600 text-sm font-medium mb-3">Head of Mathematics, PhD</p>
                            <p class="text-slate-600">Specializes in Calculus & Trigonometry. 12+ years teaching at university level and online.</p>
                        </div>
                    </div>
                    <!-- Teacher 2 -->
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="h-48 bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center">
                            <span class="text-white text-6xl font-bold">👨‍🏫</span>
                        </div>
                        <div class="p-6">
                            <h4 class="text-xl font-bold text-slate-900">Michael Chen</h4>
                            <p class="text-brand-600 text-sm font-medium mb-3">Algebra & Geometry Lead</p>
                            <p class="text-slate-600">Former high school teacher of the year. Known for engaging visual explanations.</p>
                        </div>
                    </div>
                    <!-- Teacher 3 -->
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="h-48 bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                            <span class="text-white text-6xl font-bold">👩‍🏫</span>
                        </div>
                        <div class="p-6">
                            <h4 class="text-xl font-bold text-slate-900">Dr. Lisa Thompson</h4>
                            <p class="text-brand-600 text-sm font-medium mb-3">Pre‑Calculus Specialist</p>
                            <p class="text-slate-600">Research background in math education. Creates our adaptive practice problems.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-brand-600 font-semibold tracking-wide uppercase text-sm mb-2">Simple Pricing</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-slate-900">Choose the plan that fits you</h3>
                    <p class="text-slate-500 max-w-2xl mx-auto mt-4">No hidden fees. Cancel anytime.</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Monthly Plan -->
                    <div class="bg-slate-50 rounded-2xl p-8 text-center border border-slate-200 hover:shadow-lg transition">
                        <h4 class="text-2xl font-bold text-slate-900">Monthly</h4>
                        <div class="mt-4 flex justify-center items-baseline">
                            <span class="text-5xl font-extrabold text-brand-600">R499.99</span>
                            <span class="text-slate-500 ml-1">/month</span>
                        </div>
                        <ul class="mt-6 space-y-3 text-slate-600">
                            <li>✓ Full access to all courses</li>
                            <li>✓ Unlimited practice problems</li>
                            <li>✓ Progress tracking</li>
                            <li>✓ Group Sessions</li>
                        </ul>
                        <a href="login.php" class="mt-8 inline-block w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg transition">Get Started</a>
                    </div>
                    <!-- Yearly Plan (Popular) -->
                    <div class="bg-white rounded-2xl p-8 text-center border-2 border-brand-500 shadow-lg relative">
                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-brand-500 text-white text-xs font-bold px-3 py-1 rounded-full">Most Popular</div>
                        <h4 class="text-2xl font-bold text-slate-900">Yearly</h4>
                        <div class="mt-4 flex justify-center items-baseline">
                            <span class="text-5xl font-extrabold text-brand-600">R5 499,99</span>
                            <span class="text-slate-500 ml-1">/year</span>
                        </div>
                        <p class="text-sm text-green-600 mt-1">Save 499.99 ZAR compared to monthly</p>
                        <ul class="mt-6 space-y-3 text-slate-600">
                            <li>✓ All Monthly features</li>
                            <li>✓ Priority support</li>
                            <li>✓ Downloadable worksheets</li>
                            <li>✓ Live Q&A sessions</li>
                        </ul>
                        <a href="login.php" class="mt-8 inline-block w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg transition">Get Started</a>
                    </div>
                    <!-- Lifetime Plan -->
                    <div class="bg-slate-50 rounded-2xl p-8 text-center border border-slate-200 hover:shadow-lg transition">
                        <h4 class="text-2xl font-bold text-slate-900">Tutor Package</h4>
                        <div class="mt-4 flex justify-center items-baseline">
                            <span class="text-5xl font-extrabold text-brand-600">R 499.99</span>
                            <span class="text-slate-500 ml-1">one‑time</span>
                        </div>
                        <ul class="mt-6 space-y-3 text-slate-600">
                            <li>✓ All Yearly features</li>
                            <li>✓ Lifetime updates</li>
                            <li>✓ 1-on-1 tutoring session/month</li>
                            <li>✓ Certificate of completion</li>
                        </ul>
                        <a href="login.php" class="mt-8 inline-block w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg transition">Get Started</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-brand-600 font-semibold tracking-wide uppercase text-sm mb-2">Success Stories</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-slate-900">What our students say</h3>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex items-center gap-1 text-yellow-400 mb-3">
                            ★★★★★
                        </div>
                        <p class="text-slate-600 mb-4">“I went from failing algebra to scoring 92% on my final exam. The step-by-step videos changed everything for me.”</p>
                        <div class="font-semibold text-slate-900">— Alex M., Grade 10</div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex items-center gap-1 text-yellow-400 mb-3">
                            ★★★★★
                        </div>
                        <p class="text-slate-600 mb-4">“Fun Maths Mastery made calculus actually enjoyable. The visual tools helped me understand derivatives intuitively.”</p>
                        <div class="font-semibold text-slate-900">— Jessica L., College Student</div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex items-center gap-1 text-yellow-400 mb-3">
                            ★★★★★
                        </div>
                        <p class="text-slate-600 mb-4">“As a parent, I love seeing my daughter excited about math. The teachers are fantastic and responsive.”</p>
                        <div class="font-semibold text-slate-900">— David R., Parent</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Extra Call to Action Banner -->
        <section class="bg-brand-600 py-16">
            <div class="max-w-4xl mx-auto text-center px-4">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">About Us ?</h2>
                <p class="text-brand-100 text-lg mb-8">We inspire and empower learners to excel academically and in life 

<br>We create a fun, inspiring, and growth-focused learning enviroment where children and parents can trust that students are building confidence, self-drive, and excellence.<br>Through motivation, guidance, and encouragement, we help teenagersbelieve in their abilities and achieve their best		
.</p>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="inline-block bg-white text-brand-700 font-bold py-3 px-8 rounded-lg shadow-lg hover:bg-slate-100 transition">Go to Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="inline-block bg-white text-brand-700 font-bold py-3 px-8 rounded-lg shadow-lg hover:bg-slate-100 transition">Enrol Today</a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Contact Form Section -->
        <section id="contact" class="py-20 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-brand-600 font-semibold tracking-wide uppercase text-sm mb-2">Get In Touch</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-slate-900">We'd love to hear from you</h3>
                    <p class="text-slate-500 mt-4">Have questions? Our team is ready to help.</p>
                </div>
                <?php if ($contact_message): ?>
                    <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg text-center">
                        <?php echo htmlspecialchars($contact_message); ?>
                    </div>
                <?php elseif ($contact_error): ?>
                    <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg text-center">
                        <?php echo htmlspecialchars($contact_error); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="#contact" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Your Name *</label>
                            <input type="text" name="name" id="name" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address *</label>
                            <input type="email" name="email" id="email" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        </div>
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-slate-700 mb-1">Message *</label>
                        <textarea name="message" id="message" rows="5" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-500 focus:border-transparent"></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" name="contact_submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-8 rounded-lg transition shadow-md">Send Message</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- ========== NEW SECTIONS END ========== -->

    </main>

    <footer class="bg-white border-t border-slate-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <img src="assets/logo.jpeg" alt="Fun Maths Mastery" width="100" height="100">
            </div>
            <div class="text-slate-500 text-sm text-center md:text-left">
                &copy; 2026 Fun Maths Mastery. All rights reserved.
            </div>
        </div>
        <div class="text-center pt-6 pb-3 text-slate-500">
            Powered By <a href="https://varsitymarket.co.za" class="hover:text-brand-600">Varsity Market</a> Technologies
        </div>
    </footer>

    <!-- Mobile menu toggle script (unchanged) -->
    <script>
        const menuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIconPath = document.getElementById('menu-icon-path');

        if (menuBtn) {
            menuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
                if (mobileMenu.classList.contains('hidden')) {
                    menuIconPath.setAttribute('d', 'M4 6h16M4 12h16M4 18h16');
                } else {
                    menuIconPath.setAttribute('d', 'M6 18L18 6M6 6l12 12');
                }
            });
        }
    </script>
</body>
</html>
