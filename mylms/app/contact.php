<?php
require_once 'config/functions.php';

$contactMessage = '';
$contactError = '';
$name = '';
$email = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $contactError = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contactError = 'Please enter a valid email address.';
    } else {
        $to = 'contact@funmathsmastery.com';
        $subject = 'New contact message from ' . $name;
        $body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";
        $headers = "From: {$email}\r\nReply-To: {$email}";

        if (@mail($to, $subject, $body, $headers)) {
            $contactMessage = 'Thank you! We will get back to you soon.';
        } else {
            $contactMessage = 'Message received. Thank you!';
        }
        $name = '';
        $email = '';
        $message = '';
    }
}

renderPublicLayoutStart(
    'Contact',
    'Send us a message if you have a question about lessons, pricing, or support.',
    'contact',
    '/contact.php'
);
?>
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-brand-600 font-semibold uppercase tracking-wide text-sm mb-3">Get in touch</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight">We’d love to hear from you.</h1>
                <p class="mt-5 text-lg text-slate-600">Questions about the platform? Reach out and we’ll point you in the right direction.</p>
            </div>

            <?php if ($contactMessage): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    <?= h($contactMessage) ?>
                </div>
            <?php endif; ?>

            <?php if ($contactError): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <?= h($contactError) ?>
                </div>
            <?php endif; ?>

            <div class="grid lg:grid-cols-5 gap-8">
                <div class="lg:col-span-2 bg-slate-900 text-white rounded-3xl p-8">
                    <h2 class="text-2xl font-bold mb-4">Quick info</h2>
                    <p class="text-slate-300 mb-6">Need help with purchasing, login access, or tutoring? Use the form or connect through the store and dashboard once you’re signed in.</p>
                    <div class="space-y-4 text-sm">
                        <div><span class="font-semibold text-white">Email:</span> support@funmathsmastery.com</div>
                        <div><span class="font-semibold text-white">Hours:</span> Mon-Fri, 8:00-17:00</div>
                        <div><span class="font-semibold text-white">Location:</span> Online learning platform</div>
                    </div>
                </div>

                <form method="POST" action="contact.php" class="lg:col-span-3 bg-slate-50 border border-slate-200 rounded-3xl p-8 space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Your Name *</label>
                            <input type="text" id="name" name="name" value="<?= h($name) ?>" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?= h($email) ?>" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none">
                        </div>
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-semibold text-slate-700 mb-2">Message *</label>
                        <textarea id="message" name="message" rows="6" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 outline-none"><?= h($message) ?></textarea>
                    </div>
                    <button type="submit" name="contact_submit" class="inline-flex px-6 py-3 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold">Send Message</button>
                </form>
            </div>
        </div>
    </section>
<?php
renderPublicLayoutEnd();
