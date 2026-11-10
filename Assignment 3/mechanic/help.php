<?php
require_once __DIR__ . '/db.php';
$pdo = getDB();
$mechanics = $pdo->query("SELECT * FROM mechanics ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help &amp; Workshop Guidelines — Apex Motors Appointment System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .faq-item {
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            background: #fff;
            overflow: hidden;
        }
        .faq-question {
            padding: 1.25rem 1.5rem;
            font-weight: 700;
            font-size: 1.05rem;
            color: #0f172a;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
        }
        .faq-question:hover {
            background: #f1f5f9;
        }
        .faq-answer {
            padding: 1.25rem 1.5rem;
            color: var(--text-main);
            font-size: 0.95rem;
            border-top: 1px solid var(--border);
            line-height: 1.6;
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="brand">
                <div class="brand-icon">🔧</div>
                <div class="brand-text">
                    <h1>Apex Motors Workshop</h1>
                    <span>Senior Mechanics & Auto Care Service</span>
                </div>
            </a>
            <ul class="nav-links">
                <li><a href="index.php">📅 Book Appointment</a></li>
                <li><a href="help.php" class="active">❓ Help & Rules</a></li>
                <li><a href="admin.php" class="nav-btn">🔐 Admin Portal</a></li>
            </ul>
        </div>
    </header>

    <main class="main-wrapper">
        <div class="hero-banner" style="padding: 2rem;">
            <h2>Help Center &amp; System Rules</h2>
            <p>
                Learn how our online appointment scheduling system works, why each mechanic is capped at 4 cars per day, and how to successfully reserve your preferred specialist.
            </p>
        </div>

        <div class="layout-grid">
            <div>
                <!-- FAQ Section -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">📖 Frequently Asked Questions</div>
                    </div>
                    <div class="card-body">

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Why is there a maximum limit of 4 active cars per senior mechanic?</span>
                                <span>▼</span>
                            </div>
                            <div class="faq-answer">
                                Each senior mechanic is allotted a strict maximum of <strong>4 cars per calendar day</strong>. Previously, whenever a senior mechanic was overburdened, the workshop manager reassigned vehicles to other available mechanics, which prevented clients from getting their desired specialist and led to chaos and dissatisfaction. With our online appointment system, you directly reserve your desired mechanic in advance, ensuring dedicated investigation and top quality without physical waiting.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>What happens if my desired mechanic is already fully occupied?</span>
                                <span>▼</span>
                            </div>
                            <div class="faq-answer">
                                If a senior mechanic already has 4 active appointments on your selected date, his card will be marked as <strong>Fully Occupied (0 Left)</strong> and disabled. You can either select another capable senior mechanic from our team or choose a different date where your preferred mechanic has available slots.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Can I take multiple appointments on the same date?</span>
                                <span>▼</span>
                            </div>
                            <div class="faq-answer">
                                No. To ensure fair access for all clients and prevent slot hoarding, our system validates upon submission whether a client (identified by phone number or vehicle registration number) has already taken an appointment on that date. If an appointment is already booked, the system will notify you and reject duplicate bookings for that date.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>What validation rules apply to the form fields?</span>
                                <span>▼</span>
                            </div>
                            <div class="faq-answer">
                                To ensure clean data entry, the system requires:
                                <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                                    <li><strong>Client Name:</strong> Full name (at least 2 letters, non-empty).</li>
                                    <li><strong>Phone:</strong> Digits only (0-9), between 7 and 15 numbers long.</li>
                                    <li><strong>Car Registration / License:</strong> Valid vehicle number.</li>
                                    <li><strong>Car Engine Number:</strong> Digits only stamped on the engine block.</li>
                                    <li><strong>Appointment Date:</strong> Valid date (YYYY-MM-DD), today or future dates only.</li>
                                    <li><strong>Mechanic Selection:</strong> You must click to choose an available specialist from the 5 senior mechanics.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Can an appointment date or assigned mechanic be changed later?</span>
                                <span>▼</span>
                            </div>
                            <div class="faq-answer">
                                Yes. The Workshop Admin / Manager has an administrative panel where they can view all appointments and change the date or reassign to another available mechanic upon client request, subject to available slot capacity.
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">👨‍🔧 Our 5 Senior Mechanics</div>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                            <?php foreach ($mechanics as $m): ?>
                                <div style="border: 1px solid var(--border); border-radius: 8px; padding: 1rem; background: #fff;">
                                    <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 0.5rem;">
                                        <div class="mechanic-avatar"><?= strtoupper(substr($m['name'], 0, 2)) ?></div>
                                        <div>
                                            <strong style="color: #0f172a; font-size: 1rem;"><?= htmlspecialchars($m['name']) ?></strong>
                                            <div style="font-size: 0.75rem; color: #64748b;"><?= $m['experience_years'] ?> Years Experience &bull; Senior Lead</div>
                                        </div>
                                    </div>
                                    <div style="font-size: 0.85rem; color: #334155; margin-bottom: 0.5rem;">
                                        <strong>Specialty:</strong> <?= htmlspecialchars($m['specialty']) ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: #64748b;">
                                        Direct Workshop Desk: <?= htmlspecialchars($m['phone']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <aside>
                <div class="help-card">
                    <h3>🚀 Quick Links</h3>
                    <ul style="list-style: none;">
                        <li style="margin-bottom: 0.75rem;"><a href="index.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">&rarr; Book an Appointment Now</a></li>
                        <li style="margin-bottom: 0.75rem;"><a href="admin.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">&rarr; Access Admin Dashboard</a></li>
                    </ul>
                </div>

                <div class="help-card">
                    <h3>🏢 Workshop Hours &amp; Location</h3>
                    <p style="font-size: 0.88rem; color: #334155; line-height: 1.6;">
                        <strong>Operating Hours:</strong><br>
                        Monday – Saturday: 8:00 AM – 6:00 PM<br>
                        Sunday: Closed for maintenance<br><br>
                        <strong>Physical Location:</strong><br>
                        Apex Motors Workshop Facility<br>
                        500 Industrial Parkway, Workshop Bay 4<br><br>
                        <strong>Contact:</strong><br>
                        Phone: +1 (555) 019-AUTO<br>
                        Email: appointments@apexworkshop.com
                    </p>
                </div>
            </aside>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Apex Motors Car Workshop Appointment System. All rights reserved.</p>
    </footer>

</body>
</html>
