<?php
require_once __DIR__ . '/db.php';
$pdo = getDB();

$stmt = $pdo->query("SELECT * FROM mechanics WHERE is_active = 1 ORDER BY id ASC");
$mechanics = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apex Motors Workshop — Book Appointment with Senior Mechanics</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="brand">
                <div class="brand-icon">🔧</div>
                <div class="brand-text">
                    <h1>Ishtiaq Motors</h1>
                    <span>Senior Mechanics Service</span>
                </div>
            </a>
            <ul class="nav-links">
                <li><a href="index.php" class="active">📅 Book Appointment</a></li>
                <li><a href="help.php">❓ Help & Rules</a></li>
                <li><a href="admin.php" class="nav-btn">🔐 Admin Portal</a></li>
            </ul>
        </div>
    </header>

    <main class="main-wrapper">

        <section class="hero-banner">
            <h2>Select Your Desired Senior Mechanic</h2>
            <p>
                Welcome to our online appointment scheduling portal. Choose your preferred senior specialist to investigate and service your car. To guarantee exceptional attention and quality, each senior mechanic is strictly assigned a <strong>maximum of 4 active cars per day</strong>.
            </p>
            <div class="rule-highlight">
                🛡️ Guaranteed Desired Mechanic Allocation &bull; Maximum 4 Cars Daily Per Specialist &bull; Real-Time Slot Availability
            </div>
        </section>

        <div id="formAlert" style="display: none;"></div>

        <div id="clientCheckNotice" style="display: none;"></div>

        <div class="layout-grid">
            <section class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>📝</span> Client & Vehicle Registration Form
                    </div>
                    <span style="font-size: 0.8rem; color: #64748b;"><span class="req">*</span> Required fields</span>
                </div>
                <div class="card-body">
                    <form id="appointmentForm" novalidate>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="client_name">Client Full Name <span class="req">*</span></label>
                                <input type="text" id="client_name" name="client_name" class="form-control" placeholder="e.g. Mushfiq" required>
                                <div class="invalid-feedback" id="client_name_feedback">Client name is required.</div>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number <span class="req">*</span> <small style="color: #64748b;">(Digits only)</small></label>
                                <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. 01712345678" pattern="[0-9]+" required>
                                <div class="form-text">Must contain only numbers (7 to 15 digits).</div>
                                <div class="invalid-feedback" id="phone_feedback">Phone must contain only numbers.</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Client Address <span class="req">*</span></label>
                            <input type="text" id="address" name="address" class="form-control" placeholder="e.g. 124 Parkview Avenue, City Center" required>
                            <div class="invalid-feedback" id="address_feedback">Client address is required.</div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="car_license">Car License / Registration No. <span class="req">*</span></label>
                                <input type="text" id="car_license" name="car_license" class="form-control" placeholder="e.g. DHK-METRO-GA-1122" required>
                                <div class="invalid-feedback" id="car_license_feedback">Car license/registration number is required.</div>
                            </div>

                            <div class="form-group">
                                <label for="car_engine">Car Engine Number <span class="req">*</span> <small style="color: #64748b;">(Digits only)</small></label>
                                <input type="text" id="car_engine" name="car_engine" class="form-control" placeholder="e.g. 84920153" pattern="[0-9]+" required>
                                <div class="form-text">Must contain only numbers.</div>
                                <div class="invalid-feedback" id="car_engine_feedback">Engine number must contain only numbers.</div>
                            </div>
                        </div>

                        <div class="form-group" style="background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 1rem;">
                            <label for="appointment_date" style="font-size: 1rem; color: #0f172a;">
                                📅 Choose Appointment Date <span class="req">*</span>
                            </label>
                            <input type="date" id="appointment_date" name="appointment_date" class="form-control" style="max-width: 300px; font-weight: 600;" required>
                            <div class="form-text">Selecting a date immediately queries server for each mechanic's available slots on that date.</div>
                            <div class="invalid-feedback" id="appointment_date_feedback">Valid appointment date is required.</div>
                        </div>

                        <div class="form-group" style="margin-top: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap;">
                                <label style="font-size: 1rem; color: #0f172a; margin-bottom: 0;">
                                    👨‍🔧 Select Your Desired Senior Mechanic <span class="req">*</span>
                                </label>
                                <span style="font-size: 0.8rem; color: #64748b;">Live capacity updated from server</span>
                            </div>

                            <div id="mechanicsLoading" style="display: none; padding: 1rem; text-align: center; color: var(--primary);">
                                ⏳ Checking mechanic availability with workshop server...
                            </div>

                            <div id="mechanicsContainer" class="mechanic-grid">
                                <?php foreach ($mechanics as $m): ?>
                                    <div class="mechanic-card" id="fallback_mech_<?= $m['id'] ?>">
                                        <div class="mechanic-header">
                                            <div class="mechanic-avatar"><?= strtoupper(substr($m['name'], 0, 2)) ?></div>
                                            <div>
                                                <div class="mechanic-name"><?= htmlspecialchars($m['name']) ?></div>
                                                <div style="font-size: 0.75rem; color: #64748b;"><?= $m['experience_years'] ?> Yrs Senior Experience</div>
                                            </div>
                                        </div>
                                        <div class="mechanic-spec"><?= htmlspecialchars($m['specialty']) ?></div>
                                        <div class="slot-indicator">
                                            <span class="slot-badge available">● Checking slots...</span>
                                            <span style="font-size: 0.75rem; color: #94a3b8;">Max 4 cars/day</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="invalid-feedback" id="mechanic_id_feedback" style="margin-top: 0.75rem;">Please select an available senior mechanic.</div>
                        </div>

                        <div style="margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1.5rem;">
                            <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="font-size: 1.05rem; padding: 0.9rem;">
                                Confirm & Book My Appointment
                            </button>
                            
                        </div>
                    </form>
                </div>
            </section>

    </main>

    <div class="modal-overlay" id="successModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">🎉 Appointment Confirmed!</h3>
                <button type="button" class="modal-close" id="modalCloseBtn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success" style="margin-bottom: 1rem;">
                    <span class="alert-icon">✓</span>
                    <div>
                        <strong>Approved:</strong> Your slot has been secured. Your desired mechanic has been locked in our schedule.
                    </div>
                </div>
                <div id="modalTicketContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="bookAnotherBtn">Book Another Car</button>
                <button type="button" class="btn btn-primary" id="printSlipBtn">🖨️ Print Slip</button>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> Apex Motors Car Workshop Appointment System</p>

    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
