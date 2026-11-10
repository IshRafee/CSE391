<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

$pdo = getDB();
$today = date('Y-m-d');

$totalAppts = (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$todayAppts = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = '$today' AND status != 'Cancelled'")->fetchColumn();
$approvedCount = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Approved'")->fetchColumn();
$mechanicsCount = (int)$pdo->query("SELECT COUNT(*) FROM mechanics WHERE is_active = 1")->fetchColumn();

$mechsStmt = $pdo->query("SELECT * FROM mechanics WHERE is_active = 1 ORDER BY id ASC");
$allMechanics = $mechsStmt->fetchAll();

$todayLoads = [];
$loadStmt = $pdo->query("
    SELECT mechanic_id, COUNT(*) AS cnt 
    FROM appointments 
    WHERE appointment_date = '$today' AND status != 'Cancelled' 
    GROUP BY mechanic_id
");
while ($r = $loadStmt->fetch()) {
    $todayLoads[(int)$r['mechanic_id']] = (int)$r['cnt'];
}

$apptStmt = $pdo->query("
    SELECT a.*, m.name AS mechanic_name, m.specialty AS mechanic_specialty
    FROM appointments a
    JOIN mechanics m ON a.mechanic_id = m.id
    ORDER BY a.appointment_date DESC, a.id DESC
");
$appointments = $apptStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Workshop Appointments Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .mech-bar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .mech-bar-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1rem;
        }
        .progress-bar-bg {
            background: #e2e8f0;
            border-radius: 9999px;
            height: 10px;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-container">
            <a href="admin.php" class="brand">
                <div class="brand-icon">🛠️</div>
                <div class="brand-text">
                    <h1>Workshop Admin Portal</h1>
                    <span>Manager: <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
                </div>
            </a>
            <ul class="nav-links">
                <li><a href="index.php" target="_blank">🌐 Open Client Portal</a></li>
                <li><a href="help.php" target="_blank">❓ Help & Rules</a></li>
                <li><a href="logout.php" class="btn btn-outline btn-sm" style="color: #cbd5e1; border-color: #475569;">🚪 Sign Out</a></li>
            </ul>
        </div>
    </header>

    <main class="main-wrapper">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.6rem; color: #0f172a;">Appointment Management Center</h2>
                <p style="color: var(--text-muted); font-size: 0.95rem;">
                </p>
            </div>
            <div>
                <a href="index.php" target="_blank" class="btn btn-primary">
                    
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon orange">📅</div>
                <div>
                    <div class="stat-number"><?= $totalAppts ?></div>
                    <div class="stat-label">Total Appointments</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue">🚗</div>
                <div>
                    <div class="stat-number"><?= $todayAppts ?></div>
                    <div class="stat-label">Today's Active Cars</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">👨‍🔧</div>
                <div>
                    <div class="stat-number"><?= $mechanicsCount ?></div>
                    <div class="stat-label">Senior Mechanics</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">✓</div>
                <div>
                    <div class="stat-number"><?= $approvedCount ?></div>
                    <div class="stat-label">Confirmed Bookings</div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <div class="card-title">
                    <span>📊</span> Today's Mechanic Capacity Overview (<?= $today ?>)
                </div>
                <span style="font-size: 0.8rem; color: #64748b;">Max capacity: 4 active cars per day</span>
            </div>
            <div class="card-body">
                <div class="mech-bar-grid">
                    <?php foreach ($allMechanics as $m): 
                        $bCount = $todayLoads[$m['id']] ?? 0;
                        $pct = min(100, ($bCount / 4) * 100);
                        $fillColor = $bCount >= 4 ? '#ef4444' : ($bCount >= 3 ? '#f59e0b' : '#10b981');
                    ?>
                        <div class="mech-bar-card">
                            <div style="display: flex; justify-content: space-between; align-items: baseline;">
                                <strong style="font-size: 0.95rem; color: #0f172a;"><?= htmlspecialchars($m['name']) ?></strong>
                                <span style="font-size: 0.85rem; font-weight: 700; color: <?= $fillColor ?>;">
                                    <?= $bCount ?> / 4 Cars
                                </span>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= htmlspecialchars($m['specialty']) ?>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: <?= $pct ?>%; background: <?= $fillColor ?>;"></div>
                            </div>
                            <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.35rem; display: flex; justify-content: space-between;">
                                <span><?= (4 - $bCount) > 0 ? (4 - $bCount) . ' free' : 'Fully booked' ?></span>
                                <span><?= $bCount >= 4 ? '⚠️ Capped' : 'Available' ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>📋</span> Client Appointment List
                </div>
                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                    Showing <?= count($appointments) ?> total records
                </span>
            </div>
            <div class="card-body">

                <div class="filter-bar">
                    <div>
                        <input type="text" id="searchAppointments" class="form-control" placeholder="🔍 Search name, phone, license...">
                    </div>

                    <div>
                        <select id="filterMechanic" class="form-control">
                            <option value="">All Mechanics</option>
                            <?php foreach ($allMechanics as $m): ?>
                                <option value="<?= htmlspecialchars($m['name']) ?>"><?= htmlspecialchars($m['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <select id="filterStatus" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Approved">Approved</option>
                            <option value="Rescheduled">Rescheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <input type="date" id="filterDate" class="form-control" title="Filter by appointment date">
                    </div>

                    <div>
                        <button type="button" id="resetFiltersBtn" class="btn btn-secondary btn-sm">Reset Filters</button>
                    </div>
                </div>

                <!-- Table Component -->
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Client Name</th>
                                <th>Phone</th>
                                <th>Car Registration</th>
                                <th>Car Engine</th>
                                <th>Appointment Date</th>
                                <th>Assigned Mechanic</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="appointmentsTableBody">
                            <?php if (empty($appointments)): ?>
                                <tr class="no-records-row">
                                    <td colspan="9" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                        No appointments scheduled yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($appointments as $a): 
                                    $statClass = 'badge-approved';
                                    if ($a['status'] === 'Rescheduled') $statClass = 'badge-rescheduled';
                                    if ($a['status'] === 'Cancelled') $statClass = 'badge-cancelled';
                                ?>
                                    <tr data-client="<?= strtolower(htmlspecialchars($a['client_name'])) ?>"
                                        data-phone="<?= strtolower(htmlspecialchars($a['phone'])) ?>"
                                        data-license="<?= strtolower(htmlspecialchars($a['car_license'])) ?>"
                                        data-mechanic="<?= htmlspecialchars($a['mechanic_name']) ?>"
                                        data-status="<?= htmlspecialchars($a['status']) ?>"
                                        data-date="<?= $a['appointment_date'] ?>">
                                        <td><strong>#<?= $a['id'] ?></strong></td>
                                        <td>
                                            <strong style="color: #0f172a;"><?= htmlspecialchars($a['client_name']) ?></strong>
                                            <div style="font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($a['address']) ?></div>
                                        </td>
                                        <td><code><?= htmlspecialchars($a['phone']) ?></code></td>
                                        <td>
                                            <span style="font-weight: 700; color: #1e293b; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 0.82rem;">
                                                <?= htmlspecialchars($a['car_license']) ?>
                                            </span>
                                        </td>
                                        <td><code><?= htmlspecialchars($a['car_engine']) ?></code></td>
                                        <td>
                                            <strong style="color: #d97706;"><?= $a['appointment_date'] ?></strong>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($a['mechanic_name']) ?></strong>
                                            <div style="font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($a['mechanic_specialty']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $statClass ?>"><?= $a['status'] ?></span>
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <button type="button" class="btn btn-outline btn-sm btn-edit-appointment" 
                                                    data-id="<?= $a['id'] ?>" title="Change date or reassign mechanic">
                                                ✏️ Reschedule
                                            </button>
                                            
                                            <button type="button" class="btn btn-outline btn-sm btn-view-slip" 
                                                    data-id="<?= $a['id'] ?>" title="View confirmation slip">
                                                📄 Slip
                                            </button>

                                            <?php if ($a['status'] !== 'Cancelled'): ?>
                                                <button type="button" class="btn btn-outline btn-sm btn-cancel-appointment" 
                                                        data-id="<?= $a['id'] ?>" data-client="<?= htmlspecialchars($a['client_name']) ?>" 
                                                        style="color: var(--danger); border-color: rgba(239, 68, 68, 0.3);" title="Cancel appointment">
                                                    ✕ Cancel
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline btn-sm btn-delete-appointment" 
                                                        data-id="<?= $a['id'] ?>" data-client="<?= htmlspecialchars($a['client_name']) ?>" 
                                                        style="color: #94a3b8; border-color: #cbd5e1;" title="Delete record permanently">
                                                    🗑️
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </main>

    <div class="modal-overlay" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">✏️ Reschedule &amp; Reassign Mechanic</h3>
                <button type="button" class="modal-close" id="editModalClose">&times;</button>
            </div>
            <form id="editAppointmentForm">
                <div class="modal-body">
                    <div id="editAlert" style="display: none;"></div>

                    <input type="hidden" id="edit_id" name="id">

                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; padding: 0.9rem; margin-bottom: 1.25rem;">
                        <div class="form-row">
                            <div>
                                <label style="font-size: 0.75rem; color: #64748b;">Client Name</label>
                                <input type="text" id="edit_client_name" class="form-control" readonly style="background: #e2e8f0; font-weight: 600;">
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; color: #64748b;">Phone Number</label>
                                <input type="text" id="edit_phone" class="form-control" readonly style="background: #e2e8f0;">
                            </div>
                        </div>
                        <div style="margin-top: 0.5rem;">
                            <label style="font-size: 0.75rem; color: #64748b;">Car License / Registration</label>
                            <input type="text" id="edit_car_license" class="form-control" readonly style="background: #e2e8f0; font-weight: 600;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_appointment_date">Change Appointment Date <span class="req">*</span></label>
                        <input type="date" id="edit_appointment_date" name="appointment_date" class="form-control" required>
                        <div class="form-text">Changing the date automatically checks mechanic slot limits on that date.</div>
                    </div>

                    <div class="form-group">
                        <label for="edit_mechanic_id">Reassign to Senior Mechanic <span class="req">*</span></label>
                        <select id="edit_mechanic_id" name="mechanic_id" class="form-control" required>
                        </select>
                        <div class="form-text" id="editSlotNotice" style="color: #d97706; font-weight: 500;"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_status">Appointment Status</label>
                        <select id="edit_status" name="status" class="form-control">
                            <option value="Approved">Approved</option>
                            <option value="Rescheduled">Rescheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_admin_notes">Manager Notes / Reason for Change</label>
                        <textarea id="edit_admin_notes" name="admin_notes" class="form-control" rows="2" placeholder="e.g. Client requested Tuesday due to personal schedule..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').classList.remove('active');">Cancel</button>
                    <button type="submit" id="saveEditBtn" class="btn btn-primary">Save &amp; Update Assignment</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="viewSlipModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">📄 Client Appointment Slip</h3>
                <button type="button" class="modal-close" id="viewSlipClose">&times;</button>
            </div>
            <div class="modal-body" id="adminSlipContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('viewSlipModal').classList.remove('active');">Close</button>
                <button type="button" class="btn btn-primary" id="printAdminSlipBtn">🖨️ Print Slip</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?= date('Y') ?> Apex Motors Car Workshop Management System. All rights reserved.</p>
    </footer>

    <script src="assets/js/admin.js"></script>
</body>
</html>
