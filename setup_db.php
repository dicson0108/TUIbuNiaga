<?php
/**
 * Database Setup & Seed Script
 * Taylor's University Event Management & Ibu Niaga Platform
 */

header('Content-Type: text/plain; charset=utf-8');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'tu_ibu_niaga';

try {
    // 1. Connect without db to create it if missing
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbname' ensured.\n";

    // 2. Connect to database and execute schema
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $sql = file_get_contents(__DIR__ . '/database.sql');
    $pdo->exec($sql);
    echo "Schema tables created successfully.\n";

    // 3. Clear existing seed data if needed
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $tables = [
        'audit_logs', 'system_settings', 'translations', 'help_articles',
        'ticket_replies', 'tickets', 'notifications', 'email_logs', 'enquiries',
        'bazaar_listings', 'projects', 'success_stories', 'members',
        'module_lessons', 'modules', 'event_feedback', 'event_registrations',
        'events', 'application_status_history', 'application_documents',
        'applications', 'programs', 'password_resets', 'email_verifications', 'users'
    ];
    foreach ($tables as $t) {
        $pdo->exec("TRUNCATE TABLE `$t`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Existing tables truncated for clean seed.\n";

    // 4. Seed Users
    $password_admin = password_hash('Admin@123', PASSWORD_DEFAULT);
    $password_mod = password_hash('Mod@123', PASSWORD_DEFAULT);
    $password_user = password_hash('User@123', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, language, status, phone, business_name, bio, email_verified_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Admin
    $stmt->execute(['System Admin', 'admin@taylors.edu.my', $password_admin, 'admin', 'en', 'active', '+60123456789', "Taylor's Community Relations", 'Platform Administrator and University Coordinator', '2026-09-01 00:00:00']);
    $admin_id = $pdo->lastInsertId();

    // Moderator
    $stmt->execute(['Community Moderator', 'moderator@taylors.edu.my', $password_mod, 'moderator', 'en', 'active', '+60198765432', "Taylor's Impact Lab", 'Operations and Application Reviewer', '2026-09-01 00:00:00']);
    $mod_id = $pdo->lastInsertId();

    // Main Member: Aina Rahman (from Figma design)
    $stmt->execute(['Aina Rahman', 'aina@example.com', $password_user, 'user', 'en', 'active', '+60128889999', "Aina's Kitchen", 'Passionate home baker making artisanal breakfasts and gourmet buns.', '2026-10-01 09:00:00']);
    $aina_id = $pdo->lastInsertId();

    // Member 2: Chef Rozita
    $stmt->execute(['Rozita Che Wan', 'rozita@example.com', $password_user, 'user', 'bm', 'active', '+60134445555', 'Dapur Rozita', 'Traditional Malay delicacies and artisan catering.', '2026-10-02 10:00:00']);
    $rozita_id = $pdo->lastInsertId();

    // Member 3: Siti Aminah
    $stmt->execute(['Siti Aminah', 'siti@example.com', $password_user, 'user', 'zh', 'active', '+60167778888', 'Aminah Crafts & Bakes', 'Handcrafted gift sets, cookies, and catering services.', '2026-10-03 11:00:00']);
    $siti_id = $pdo->lastInsertId();

    echo "Users seeded.\n";

    // 5. Seed Programs
    $stmtProg = $pdo->prepare("INSERT INTO programs (title, code, description, requirements, status, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtProg->execute([
        'Ibu Niaga Entrepreneurship Cohort 2026',
        'IN-2026-Q4',
        'Comprehensive 12-week micro-entrepreneurship incubator for women with hands-on pastry, culinary, and business literacy tracks hosted at Taylor\'s University Lakeside Campus.',
        'Malaysian citizen or permanent resident, aspiring or early-stage female micro-entrepreneur, committed to weekly weekend workshop attendance.',
        'open',
        '2026-11-01',
        '2027-01-30'
    ]);
    $prog_id = $pdo->lastInsertId();

    $stmtProg->execute([
        'Digital Literacy & Social Commerce Bootcamp',
        'DL-2026-01',
        'Intensive 4-week workshop series covering e-commerce product photography, live selling tactics, and WhatsApp business automation.',
        'Open to all verified Ibu Niaga registered members.',
        'open',
        '2026-11-15',
        '2026-12-15'
    ]);

    echo "Programs seeded.\n";

    // 6. Seed Application for Aina Rahman (Matches Figma: "Pastry Skills application is being reviewed")
    $stmtApp = $pdo->prepare("INSERT INTO applications (application_no, user_id, program_id, full_name, ic_no, phone, address, business_name, business_category, business_stage, monthly_revenue, statement_of_intent, status, reviewer_id, remarks, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtApp->execute([
        'IN-0142',
        $aina_id,
        $prog_id,
        'Aina Rahman',
        '881024-10-5542',
        '+60128889999',
        'No. 18, Jalan USJ 11/2, 47620 Subang Jaya, Selangor',
        "Aina's Kitchen",
        'Food & Beverage',
        'Early Revenue (1-2 years)',
        'RM 2,500 - RM 4,000',
        'I want to master commercial pastry skills and elevate my packaging to supply local cafes around Subang Jaya and Taylor\'s Lakeside campus.',
        'In Review',
        $mod_id,
        'Application moved to In Review. Verification of MyKad and business profile completed.',
        '2026-10-16 09:30:00'
    ]);
    $app_id = $pdo->lastInsertId();

    // Application document
    $stmtDoc = $pdo->prepare("INSERT INTO application_documents (application_id, doc_type, file_name, file_path, file_size, mime_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtDoc->execute([$app_id, 'IC Copy', 'ic_aina_rahman.pdf', 'uploads/documents/sample_ic.pdf', 1048576, 'application/pdf']);
    $stmtDoc->execute([$app_id, 'Business Proposal', 'ainas_kitchen_pitch.pdf', 'uploads/documents/sample_proposal.pdf', 2097152, 'application/pdf']);

    // Application status history
    $stmtHist = $pdo->prepare("INSERT INTO application_status_history (application_id, status, changed_by, note, changed_at) VALUES (?, ?, ?, ?, ?)");
    $stmtHist->execute([$app_id, 'Submitted', $aina_id, 'Initial application submitted online.', '2026-10-15 14:20:00']);
    $stmtHist->execute([$app_id, 'In Review', $mod_id, 'Review initiated by Community Operations Team.', '2026-10-16 09:30:00']);

    // Second Application for Aina (Approved for another track)
    $stmtApp->execute([
        'IN-0098',
        $aina_id,
        $prog_id,
        'Aina Rahman',
        '881024-10-5542',
        '+60128889999',
        'No. 18, Jalan USJ 11/2, 47620 Subang Jaya, Selangor',
        "Aina's Kitchen",
        'Food & Beverage',
        'Early Revenue',
        'RM 2,000',
        'Participate in Community Incubation & Mentorship.',
        'Approved',
        $mod_id,
        'Congratulations! You have been accepted into the Community Mentorship program.',
        '2026-09-10 10:00:00'
    ]);
    $app2_id = $pdo->lastInsertId();
    $stmtHist->execute([$app2_id, 'Submitted', $aina_id, 'Submitted', '2026-09-10 10:00:00']);
    $stmtHist->execute([$app2_id, 'Approved', $mod_id, 'Application approved.', '2026-09-14 11:30:00']);

    echo "Applications seeded.\n";

    // 7. Seed Events (Matches Figma: Bake your next chapter 24 Oct 2026, Small steps stronger finances 31 Oct, Meet share grow 07 Nov)
    $stmtEvent = $pdo->prepare("INSERT INTO events (title, category, type, venue, capacity, start_at, end_at, poster_path, description, speaker, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmtEvent->execute([
        'Bake your next chapter',
        'Workshop',
        'Physical',
        "Taylor's Lakeside Campus · Kitchen Studio",
        24,
        '2026-10-24 09:00:00',
        '2026-10-24 13:00:00',
        'assets/images/20 Sample Photography - Kitchen.png',
        'A hands-on pastry workshop with our community mentors. Learn sourdough techniques, oven management, and pastry laminations in state-of-the-art commercial culinary labs.',
        'Chef Rozita & Pastry Faculty Mentors',
        'Upcoming',
        $admin_id
    ]);
    $ev1_id = $pdo->lastInsertId();

    $stmtEvent->execute([
        'Small steps, stronger finances',
        'Financial Literacy',
        'Online',
        'Zoom Webinar & Interactive Q&A',
        40,
        '2026-10-31 10:00:00',
        '2026-10-31 12:30:00',
        'assets/images/22 Sample Photography - Community.png',
        'Build confidence with pricing, cash flow, unit economics, and everyday business decisions tailored for food entrepreneurs and home crafters.',
        'Associate Prof. Dr. Lim & Industry Advisors',
        'Upcoming',
        $admin_id
    ]);
    $ev2_id = $pdo->lastInsertId();

    $stmtEvent->execute([
        'Meet, share, grow',
        'Community',
        'Physical',
        "Taylor's Lakeside Campus · The Grand Hall",
        50,
        '2026-11-07 14:00:00',
        '2026-11-07 17:30:00',
        'assets/images/22 Sample Photography - Community.png',
        'Make meaningful connections with women building businesses like yours. Peer feedback circle, product sampling, and collaborative network building.',
        'Ibu Niaga Community Council',
        'Upcoming',
        $admin_id
    ]);
    $ev3_id = $pdo->lastInsertId();

    // Past completed event for feedback demo
    $stmtEvent->execute([
        'Artisan Bread & Sourdough Fundamentals',
        'Workshop',
        'Physical',
        "Taylor's Lakeside Campus · Commercial Bakery",
        20,
        '2026-09-20 09:00:00',
        '2026-09-20 13:00:00',
        'assets/images/21 Sample Photography - Breakfast.png',
        'Foundation baking principles, hydration control, and crumb structure evaluation.',
        'Chef Jean-Pierre',
        'Completed',
        $admin_id
    ]);
    $ev4_id = $pdo->lastInsertId();

    // Event registrations for Aina
    $stmtReg = $pdo->prepare("INSERT INTO event_registrations (event_id, user_id, status, joined_at) VALUES (?, ?, ?, NOW())");
    $stmtReg->execute([$ev1_id, $aina_id, 'joined']);
    $stmtReg->execute([$ev2_id, $aina_id, 'joined']);
    $stmtReg->execute([$ev3_id, $aina_id, 'joined']);
    $stmtReg->execute([$ev4_id, $aina_id, 'joined']);

    // Event feedback
    $stmtFb = $pdo->prepare("INSERT INTO event_feedback (event_id, user_id, rating, comment, status) VALUES (?, ?, ?, ?, ?)");
    $stmtFb->execute([$ev4_id, $aina_id, 5, 'The hands-on guidance was extraordinary! My bread crust improved immediately. Highly recommended!', 'visible']);
    $stmtFb->execute([$ev4_id, $rozita_id, 5, 'Taylor\'s commercial kitchen facilities are world-class. Looking forward to more sessions.', 'visible']);

    echo "Events and registrations seeded.\n";

    // 8. Seed Modules (Matches PRD 5.3 & Figma: Emotional Support, Pastry Skills, Financial Literacy, Table Setting Etiquette)
    $stmtMod = $pdo->prepare("INSERT INTO modules (title, category, overview, lessons_count, schedule, resources, poster_path, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmtMod->execute([
        'Emotional Support',
        'Wellbeing',
        'A supportive space to build emotional resilience, self-confidence, stress management, and empathetic peer connections.',
        4,
        'Saturdays · 10:00 AM - 12:00 PM',
        'Journaling Workbook (PDF), Mindfulness Audio Track, Community Discord Circle',
        'assets/images/22 Sample Photography - Community.png',
        1
    ]);
    $mod1_id = $pdo->lastInsertId();

    $stmtMod->execute([
        'Pastry Skills',
        'Practical Skills',
        'From the first fold to your first order. Learn at your own pace with commercial bakers covering tarts, sourdough, and laminated viennoiserie.',
        6,
        'Tuesdays & Thursdays · 2:00 PM - 5:00 PM',
        'Baking Standard Formulation Manual, Oven Calibration Guide, Supplier Directory',
        'assets/images/20 Sample Photography - Kitchen.png',
        2
    ]);
    $mod2_id = $pdo->lastInsertId();

    $stmtMod->execute([
        'Financial Literacy',
        'Business',
        'Understand your numbers, separate household finances from business accounts, price for profitability, and master cash flow projections.',
        5,
        'Wednesdays · 8:00 PM - 9:30 PM (Online)',
        'Excel Costing Template, Halal Certification Guidance, Microloan Application Checklist',
        'assets/images/21 Sample Photography - Breakfast.png',
        3
    ]);
    $mod3_id = $pdo->lastInsertId();

    $stmtMod->execute([
        'Table Setting Etiquette',
        'Hospitality',
        'Master the art of presentation, banquet table layout, upscale cutlery conventions, and refined hospitality touchpoints.',
        3,
        'Fridays · 10:00 AM - 1:00 PM',
        'Banquet Presentation Deck, Napkin Folding Guide, Hygiene Protocol Handbook',
        'assets/images/20 Sample Photography - Kitchen.png',
        4
    ]);
    $mod4_id = $pdo->lastInsertId();

    // Module Lessons
    $stmtLes = $pdo->prepare("INSERT INTO module_lessons (module_id, title, duration, content, sort_order) VALUES (?, ?, ?, ?, ?)");
    $stmtLes->execute([$mod2_id, '1. Introduction to Pastry Tools & Safety', '45 mins', 'Understanding commercial mixers, safety hygiene, precision digital scales, and deck oven setup.', 1]);
    $stmtLes->execute([$mod2_id, '2. The Science of Dough Hydration & Yeast', '60 mins', 'Controlling fermentation temperatures, gluten development, and yeast hydration ratios.', 2]);
    $stmtLes->execute([$mod2_id, '3. Viennoiserie & Lamination Fundamentals', '90 mins', 'Mastering butter block temperature, 3-fold turns, and achieving crisp flaky layers.', 3]);
    $stmtLes->execute([$mod2_id, '4. Sweet Tarts & Blind Baking Techniques', '60 mins', 'Pâte Sablée preparation, tart shell shrinkage prevention, and silky custard fillings.', 4]);
    $stmtLes->execute([$mod2_id, '5. Product Packaging & Shelf-life Testing', '45 mins', 'Eco-friendly biodegradable packaging, silica gels, and freshness retention.', 5]);
    $stmtLes->execute([$mod2_id, '6. Menu Costing & Profit Margin Calculation', '50 mins', 'Calculating food cost percentage, portion yield, and pricing for retail and wholesale.', 6]);

    echo "Modules and lessons seeded.\n";

    // 9. Seed Members Directory & Success Stories
    $stmtMem = $pdo->prepare("INSERT INTO members (user_id, name, business_name, category, bio, photo_path, location, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtMem->execute([
        $aina_id,
        'Aina Rahman',
        "Aina's Kitchen",
        'Food & Beverages',
        'Founder of Aina\'s Kitchen in Subang Jaya. Specializes in wholesome homemade breakfast boxes, artisanal brioche buns, and gourmet spreads prepared fresh each morning.',
        'assets/images/21 Sample Photography - Breakfast.png',
        'Subang Jaya',
        1
    ]);
    $mem_aina_id = $pdo->lastInsertId();

    $stmtMem->execute([
        $rozita_id,
        'Rozita Che Wan',
        'Dapur Rozita',
        'Home Bakery',
        'Passionate culinary artisan offering traditional kuih-muih, weekend pastry selections, and catering for university and corporate gatherings.',
        'assets/images/20 Sample Photography - Kitchen.png',
        'Petaling Jaya',
        1
    ]);

    $stmtMem->execute([
        $siti_id,
        'Siti Aminah',
        'Aminah Crafts & Bakes',
        'Services & Catering',
        'Community catering and handmade festive gift hampers created with sustainable packaging and farm-fresh ingredients.',
        'assets/images/22 Sample Photography - Community.png',
        'Shah Alam',
        1
    ]);

    // Success Story (Matches Figma Spotlight: "I started with a small idea. Now I believe in what comes next." - Aina Rahman, Aina's Kitchen)
    $stmtStory = $pdo->prepare("INSERT INTO success_stories (member_id, title, quote, body, photo_path, published_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtStory->execute([
        $mem_aina_id,
        'From kitchen stove to campus orders: Aina\'s transformative journey',
        'I started with a small idea. Now I believe in what comes next.',
        'When Aina Rahman joined the Ibu Niaga initiative at Taylor\'s University, she had a single domestic oven and a dream to scale her home bakery. Through the structured Pastry Skills pathway and direct mentorship from Taylor\'s School of Food Studies & Gastronomy, she learned batch consistency, commercial sanitation, and digital social selling. Today, Aina\'s Kitchen supplies wholesome breakfast boxes to over 15 campus events each month and has expanded her micro-kitchen to support two other mothers in her neighborhood.',
        'assets/images/22 Sample Photography - Community.png',
        '2026-10-10'
    ]);

    // Past Projects
    $stmtProj = $pdo->prepare("INSERT INTO projects (title, summary, outcomes, category, poster_path, year, gallery_json) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtProj->execute([
        'Subang Community Kitchen Collective',
        'A collaborative initiative between Taylor\'s University culinary mentors and 25 community micro-bakers to establish shared commercial prep spaces.',
        'Graduated 25 certified food entrepreneurs; generated RM 45,000 in collaborative catering revenue during university symposiums.',
        'Community Incubation',
        'assets/images/20 Sample Photography - Kitchen.png',
        '2026',
        json_encode(['assets/images/20 Sample Photography - Kitchen.png', 'assets/images/21 Sample Photography - Breakfast.png'])
    ]);

    $stmtProj->execute([
        'Ibu Digital Marketplace Pilot',
        'Empowering home-based female producers with unified packaging design, QR payment integration, and university bazaar showcases.',
        'Supported 42 home businesses; facilitated over 1,200 direct local orders through digital cataloging.',
        'Digital Commerce',
        'assets/images/21 Sample Photography - Breakfast.png',
        '2025',
        json_encode(['assets/images/21 Sample Photography - Breakfast.png', 'assets/images/22 Sample Photography - Community.png'])
    ]);

    echo "Members, stories, and projects seeded.\n";

    // 10. Seed Open Bazaar Listings (Matches Figma: Homemade breakfast box, Weekend pastry selection, Community catering)
    $stmtBaz = $pdo->prepare("INSERT INTO bazaar_listings (user_id, title, category, description, price_text, photos_json, location, status, is_featured, views_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmtBaz->execute([
        $aina_id,
        'Homemade breakfast box',
        'Food & Beverages',
        'Freshly prepared with care by Aina\'s Kitchen. Includes freshly baked artisan croissant, pasture-raised egg scramble, caramelized banana toast, and seasonal berries.',
        'From RM 25',
        json_encode(['assets/images/21 Sample Photography - Breakfast.png']),
        'Subang Jaya',
        'published',
        1,
        142
    ]);
    $baz1_id = $pdo->lastInsertId();

    $stmtBaz->execute([
        $rozita_id,
        'Weekend pastry selection',
        'Home Bakery',
        'Small-batch favourites made for sharing. Box includes 2 pain au chocolat, 2 almond frangipane tarts, and cinnamon morning buns. Pre-orders open Thursday.',
        'From RM 35',
        json_encode(['assets/images/20 Sample Photography - Kitchen.png']),
        'Petaling Jaya',
        'published',
        1,
        98
    ]);
    $baz2_id = $pdo->lastInsertId();

    $stmtBaz->execute([
        $siti_id,
        'Community catering package',
        'Services & Catering',
        'Thoughtful food for your next gathering or university department meeting. Customizable menu featuring local savory pastries, fresh juices, and sustainable tableware.',
        'Request a quote',
        json_encode(['assets/images/22 Sample Photography - Community.png']),
        'Taylor\'s Lakeside Campus & Subang',
        'published',
        1,
        65
    ]);
    $baz3_id = $pdo->lastInsertId();

    // Enquiries for Aina's listings (Matches Figma: "4 new enquiries, 2 need a reply")
    $stmtEnq = $pdo->prepare("INSERT INTO enquiries (listing_id, seller_id, sender_name, sender_email, sender_phone, message, status, reply_text, replied_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmtEnq->execute([
        $baz1_id,
        $aina_id,
        'Dr. Faridah Noor',
        'faridah@taylors.edu.my',
        '+60123334444',
        'Hi Aina, we would like to order 12 breakfast boxes for our faculty morning briefing next Tuesday at 8:30 AM. Can delivery be made to Block D?',
        'new',
        NULL,
        NULL,
        '2026-10-18 08:40:00'
    ]);

    $stmtEnq->execute([
        $baz1_id,
        $aina_id,
        'Marcus Tan',
        'marcus.tan@example.com',
        '+60172221111',
        'Are there vegetarian or gluten-free options available in the breakfast box?',
        'new',
        NULL,
        NULL,
        '2026-10-17 16:15:00'
    ]);

    $stmtEnq->execute([
        $baz1_id,
        $aina_id,
        'Nurul Izzah',
        'nurul@example.com',
        '+60195556666',
        'Can I arrange pickup from your kitchen in USJ 11 this Saturday morning?',
        'replied',
        'Hi Nurul! Yes, Saturday morning pickup between 8:30 AM and 10:30 AM is perfect. Looking forward to preparing your order!',
        '2026-10-17 11:00:00',
        '2026-10-17 09:30:00'
    ]);

    $stmtEnq->execute([
        $baz1_id,
        $aina_id,
        'Kevin Wong',
        'kevin@example.com',
        '+60124443333',
        'Inquiring regarding corporate gift catering for Christmas season.',
        'replied',
        'Hello Kevin! Yes, our seasonal corporate menu will be released next week. I will email you our catalog.',
        '2026-10-16 15:00:00',
        '2026-10-16 10:00:00'
    ]);

    echo "Bazaar listings and enquiries seeded.\n";

    // 11. Seed Notifications (Matches Figma Member Dashboard: 3 unread notifications)
    $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link, read_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtNotif->execute([
        $aina_id,
        'event',
        'Registration Confirmed',
        'Your registration for Bake your next chapter (24 Oct 2026) has been confirmed.',
        'member/events.php',
        NULL,
        '2026-10-18 08:40:00'
    ]);
    $stmtNotif->execute([
        $aina_id,
        'enquiry',
        'New Listing Enquiry',
        'Dr. Faridah Noor sent a new order inquiry regarding your Homemade breakfast box.',
        'member/enquiries.php',
        NULL,
        '2026-10-18 08:40:00'
    ]);
    $stmtNotif->execute([
        $aina_id,
        'application',
        'Application Update',
        'Your Pastry Skills application (IN-0142) was moved to In Review by the review committee.',
        'member/applications.php',
        NULL,
        '2026-10-16 09:30:00'
    ]);

    // 12. Seed Support Tickets (Matches Figma: #SUP-0142, Open, Assigned)
    $stmtTick = $pdo->prepare("INSERT INTO tickets (ticket_no, user_id, category, priority, subject, status, assigned_to, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtTick->execute([
        'SUP-0142',
        $aina_id,
        'Program Application',
        'Medium',
        'Document re-upload inquiry for business registration SSM',
        'In Progress',
        $mod_id,
        '2026-10-18 09:00:00'
    ]);
    $tick1_id = $pdo->lastInsertId();

    $stmtReply = $pdo->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message, created_at) VALUES (?, ?, ?, ?)");
    $stmtReply->execute([
        $tick1_id,
        $aina_id,
        'Hello team, I recently renewed my SSM enterprise registration certificate. Should I upload the new copy or does my existing submission suffice?',
        '2026-10-18 09:00:00'
    ]);
    $stmtReply->execute([
        $tick1_id,
        $mod_id,
        'Hi Aina, please re-upload your latest SSM certificate under your application page so our verification team can attach the newest validity date to your profile.',
        '2026-10-18 09:30:00'
    ]);

    echo "Notifications and support tickets seeded.\n";

    // 13. Seed Help Center Articles
    $stmtHelp = $pdo->prepare("INSERT INTO help_articles (category, title, slug, body, language, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtHelp->execute([
        'Program Registration',
        'What documents are required to apply for the Ibu Niaga program?',
        'what-documents-are-required',
        'Applicants need to provide a valid copy of their MyKad / NRIC and a brief business proposal or description of their home craft. If you possess an SSM enterprise registration certificate, you may also attach it, though home-based startups are welcome to apply without prior formal registration.',
        'en',
        1
    ]);
    $stmtHelp->execute([
        'Program Registration',
        'How do I track my application status?',
        'how-do-i-track-status',
        'Once submitted, log into your Member Workspace and click "My Applications". A real-time 5-stage progress indicator displays whether your submission is Submitted, In Review, Needs Documents, Approved, or Rejected along with reviewer notes.',
        'en',
        2
    ]);
    $stmtHelp->execute([
        'Events & Workshops',
        'How does the event waitlist work?',
        'how-does-waitlist-work',
        'When an event reaches its physical kitchen or studio capacity, subsequent registrations are automatically placed on a waitlist. If an attendee cancels, the system automatically promotes the next waitlisted applicant and sends an instant email notification.',
        'en',
        3
    ]);
    $stmtHelp->execute([
        'Open Bazaar',
        'Can I sell directly through the Open Bazaar?',
        'selling-through-open-bazaar',
        'The Open Bazaar operates as an interactive showcase and lead generation platform. Customers submit structured enquiries or order requests directly to your seller dashboard and inbox. Payment is arranged directly with the seller according to your preferred banking or cash terms.',
        'en',
        4
    ]);

    // 14. Seed System Settings
    $stmtSet = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
    $stmtSet->execute(['bazaar_approval_required', '1', 'Require Moderator review and approval before bazaar listings are published publicly.']);
    $stmtSet->execute(['max_upload_size_mb', '5', 'Maximum document and photo upload size in megabytes.']);
    $stmtSet->execute(['enable_email_notifications', '1', 'Enable automated notification emails to members and applicants.']);
    $stmtSet->execute(['smtp_host', 'localhost', 'SMTP server host address.']);
    $stmtSet->execute(['smtp_port', '587', 'SMTP server port.']);
    $stmtSet->execute(['smtp_user', '', 'SMTP authentication username.']);
    $stmtSet->execute(['smtp_pass', '', 'SMTP authentication password.']);
    $stmtSet->execute(['smtp_from_email', 'no-reply@taylors.edu.my', 'Outgoing sender email address.']);
    $stmtSet->execute(['smtp_from_name', "Taylor's Ibu Niaga Platform", 'Outgoing sender display name.']);

    // 15. Seed Audit Logs
    $stmtAudit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, details, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtAudit->execute([$mod_id, 'UPDATE_STATUS', 'applications', $app_id, 'Status changed to In Review', '127.0.0.1', '2026-10-16 09:30:00']);
    $stmtAudit->execute([$admin_id, 'CREATE_EVENT', 'events', $ev1_id, 'Created event: Bake your next chapter', '127.0.0.1', '2026-10-14 10:00:00']);
    $stmtAudit->execute([$admin_id, 'SYSTEM_INIT', 'system', 1, 'Database initialization and seeding completed.', '127.0.0.1', '2026-10-18 21:00:00']);

    // 16. Seed Multi-Language Translations (EN, BM, ZH)
    $translations = [
        // Navigation & General
        'nav_about' => ['en' => 'About', 'bm' => 'Tentang Kami', 'zh' => '关于我们'],
        'nav_projects' => ['en' => 'Projects', 'bm' => 'Projek', 'zh' => '项目'],
        'nav_members' => ['en' => 'Members', 'bm' => 'Ahli', 'zh' => '会员名录'],
        'nav_stories' => ['en' => 'Stories', 'bm' => 'Kisah Kejayaan', 'zh' => '成功故事'],
        'nav_modules' => ['en' => 'Modules', 'bm' => 'Modul', 'zh' => '技能模块'],
        'nav_events' => ['en' => 'Events', 'bm' => 'Acara', 'zh' => '活动'],
        'nav_bazaar' => ['en' => 'Bazaar', 'bm' => 'Bazar', 'zh' => '公开集市'],
        'nav_help' => ['en' => 'Help Center', 'bm' => 'Pusat Bantuan', 'zh' => '帮助中心'],
        'nav_contact' => ['en' => 'Contact', 'bm' => 'Hubungi Kami', 'zh' => '联系我们'],
        'btn_login' => ['en' => 'Log in', 'bm' => 'Log Masuk', 'zh' => '登录'],
        'btn_register' => ['en' => 'Register', 'bm' => 'Daftar', 'zh' => '注册'],
        'btn_logout' => ['en' => 'Log out', 'bm' => 'Log Keluar', 'zh' => '退出登录'],
        
        // Hero & Homepage
        'hero_tag' => ['en' => 'WOMEN. COMMUNITY. POSSIBILITY.', 'bm' => 'WANITA. KOMUNITI. PELUANG.', 'zh' => '女性 · 社区 · 无限可能'],
        'hero_title' => ['en' => 'Your next chapter starts here.', 'bm' => 'Bab seterusnya anda bermula di sini.', 'zh' => '属于您的崭新篇章，自此启航。'],
        'hero_subtitle' => ['en' => 'Build skills, find your people and turn your business dreams into everyday possibilities with Ibu Niaga.', 'bm' => 'Bina kemahiran, temui komuniti anda dan jadikan impian perniagaan anda satu kenyataan dengan Ibu Niaga.', 'zh' => '与 Ibu Niaga 携手，提升专业技能、结识互助伙伴，将您的创业梦想化为日常可能。'],
        'btn_join_community' => ['en' => 'Join our community', 'bm' => 'Sertai komuniti kami', 'zh' => '加入我们的社区'],
        'btn_browse_events' => ['en' => 'Browse events', 'bm' => 'Lihat acara', 'zh' => '浏览活动'],
        'stat_women' => ['en' => 'Women connected', 'bm' => 'Wanita terhubung', 'zh' => '赋能女性创业者'],
        'stat_pathways' => ['en' => 'Skills pathways', 'bm' => 'Laluan kemahiran', 'zh' => '技能实训路径'],
        'stat_events' => ['en' => 'Community events', 'bm' => 'Acara komuniti', 'zh' => '社区共创活动'],
        'stat_businesses' => ['en' => 'Local businesses', 'bm' => 'Perniagaan tempatan', 'zh' => '本土微型企业'],

        // Registration form
        'reg_title' => ['en' => 'Create your account', 'bm' => 'Cipta akaun anda', 'zh' => '创建您的账户'],
        'reg_subtitle' => ['en' => 'Join Taylor\'s Ibu Niaga community and step into your entrepreneurial potential.', 'bm' => 'Sertai komuniti Ibu Niaga Taylor dan kembangkan potensi keusahawanan anda.', 'zh' => '加入泰莱大学 Ibu Niaga 社区，开启您的微创业潜力。'],
        'form_name' => ['en' => 'Full Name', 'bm' => 'Nama Penuh', 'zh' => '全名'],
        'form_email' => ['en' => 'Email Address', 'bm' => 'Alamat Emel', 'zh' => '电子邮箱'],
        'form_password' => ['en' => 'Password', 'bm' => 'Kata Laluan', 'zh' => '密码'],
        'form_confirm_password' => ['en' => 'Confirm Password', 'bm' => 'Sahkan Kata Laluan', 'zh' => '确认密码'],
        'form_language' => ['en' => 'Preferred Language', 'bm' => 'Bahasa Pilihan', 'zh' => '首选语言'],
        'form_submit_reg' => ['en' => 'Register account', 'bm' => 'Daftar Akaun', 'zh' => '注册账号'],
        
        // Workspace
        'ws_member' => ['en' => 'USER WORKSPACE', 'bm' => 'RUANG KERJA PENGGUNA', 'zh' => '会员工作台'],
        'ws_moderator' => ['en' => 'MODERATOR WORKSPACE', 'bm' => 'RUANG KERJA MODERATOR', 'zh' => '审核员工作台'],
        'ws_admin' => ['en' => 'ADMIN WORKSPACE', 'bm' => 'RUANG KERJA PENTADBIR', 'zh' => '管理员工作台'],
        'ws_dashboard' => ['en' => 'Dashboard', 'bm' => 'Papan Pemuka', 'zh' => '控制面板'],
        'ws_applications' => ['en' => 'My Applications', 'bm' => 'Permohonan Saya', 'zh' => '我的申请'],
        'ws_events' => ['en' => 'My Events', 'bm' => 'Acara Saya', 'zh' => '我的活动'],
        'ws_bazaar' => ['en' => 'My Bazaar Listings', 'bm' => 'Senarai Bazar Saya', 'zh' => '我的集市商品'],
        'ws_enquiries' => ['en' => 'Enquiries', 'bm' => 'Pertanyaan', 'zh' => '顾客咨询'],
        'ws_notifications' => ['en' => 'Notifications', 'bm' => 'Pemberitahuan', 'zh' => '系统消息'],
        'ws_tickets' => ['en' => 'Support Tickets', 'bm' => 'Tiket Sokongan', 'zh' => '技术支持工单'],
        'footer_taylors' => ['en' => 'A community initiative by Taylor\'s University', 'bm' => 'Inisiatif komuniti oleh Taylor\'s University', 'zh' => '泰莱大学社区赋能计划']
    ];

    $stmtTrans = $pdo->prepare("INSERT INTO translations (trans_key, lang, trans_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE trans_value = VALUES(trans_value)");
    foreach ($translations as $key => $langs) {
        foreach ($langs as $lang => $val) {
            $stmtTrans->execute([$key, $lang, $val]);
        }
    }

    echo "Multi-language translations seeded.\n";
    echo "\n=== Database setup completed successfully! ===\n";
    echo "Default Accounts:\n";
    echo "  Admin:     admin@taylors.edu.my  / Admin@123\n";
    echo "  Moderator: moderator@taylors.edu.my / Mod@123\n";
    echo "  Member:    aina@example.com      / User@123\n";

} catch (Exception $e) {
    echo "ERROR during setup: " . $e->getMessage() . "\n";
    exit(1);
}
