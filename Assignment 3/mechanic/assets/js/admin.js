/**
 * Workshop Online Appointment System - Admin JavaScript
 * Appointment listing, real-time reschedule/reassign modal, live slot check & cancellation
 */

document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editModal');
    const editModalClose = document.getElementById('editModalClose');
    const editForm = document.getElementById('editAppointmentForm');
    const editDateInput = document.getElementById('edit_appointment_date');
    const editMechanicSelect = document.getElementById('edit_mechanic_id');
    const editSlotNotice = document.getElementById('editSlotNotice');
    const editAlert = document.getElementById('editAlert');

    // Filter elements
    const searchInput = document.getElementById('searchAppointments');
    const filterMechanic = document.getElementById('filterMechanic');
    const filterStatus = document.getElementById('filterStatus');
    const filterDate = document.getElementById('filterDate');
    const tableRows = document.querySelectorAll('#appointmentsTableBody tr');

    // 1. Table Search & Filter Handler
    function filterTable() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const mech = filterMechanic ? filterMechanic.value.toLowerCase() : '';
        const stat = filterStatus ? filterStatus.value.toLowerCase() : '';
        const fDate = filterDate ? filterDate.value : '';

        tableRows.forEach(row => {
            if (row.classList.contains('no-records-row')) return;

            const name = row.getAttribute('data-client') || '';
            const phone = row.getAttribute('data-phone') || '';
            const license = row.getAttribute('data-license') || '';
            const mechanic = row.getAttribute('data-mechanic') || '';
            const status = row.getAttribute('data-status') || '';
            const date = row.getAttribute('data-date') || '';

            const matchesQuery = !query || name.includes(query) || phone.includes(query) || license.includes(query);
            const matchesMech = !mech || mechanic.toLowerCase() === mech;
            const matchesStatus = !stat || status.toLowerCase() === stat;
            const matchesDate = !fDate || date === fDate;

            if (matchesQuery && matchesMech && matchesStatus && matchesDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (filterMechanic) filterMechanic.addEventListener('change', filterTable);
    if (filterStatus) filterStatus.addEventListener('change', filterTable);
    if (filterDate) filterDate.addEventListener('input', filterTable);

    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            if (filterMechanic) filterMechanic.value = '';
            if (filterStatus) filterStatus.value = '';
            if (filterDate) filterDate.value = '';
            filterTable();
        });
    }

    // 2. Edit / Reschedule Modal Logic
    document.querySelectorAll('.btn-edit-appointment').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            openEditModal(id);
        });
    });

    function openEditModal(id) {
        if (!editModal) return;

        // Clear alerts
        if (editAlert) editAlert.style.display = 'none';
        if (editSlotNotice) editSlotNotice.style.display = 'none';

        // Fetch appointment details
        fetch(`api/get_appointment.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Error loading appointment: ' + (data.message || 'Not found'));
                    return;
                }

                const appt = data.appointment;
                document.getElementById('edit_id').value = appt.id;
                document.getElementById('edit_client_name').value = appt.client_name;
                document.getElementById('edit_phone').value = appt.phone;
                document.getElementById('edit_car_license').value = appt.car_license;
                document.getElementById('edit_appointment_date').value = appt.appointment_date;
                document.getElementById('edit_status').value = appt.status;
                document.getElementById('edit_admin_notes').value = appt.admin_notes || '';

                // Store current mechanic ID
                editMechanicSelect.setAttribute('data-current-mechanic', appt.mechanic_id);

                // Fetch mechanics and update slots for this date
                fetchMechanicsForModal(appt.appointment_date, appt.mechanic_id, appt.id);

                editModal.classList.add('active');
            })
            .catch(err => {
                alert('Network error loading appointment details.');
                console.error(err);
            });
    }

    if (editDateInput) {
        editDateInput.addEventListener('change', function () {
            const apptId = document.getElementById('edit_id').value;
            const currentMechId = editMechanicSelect.value;
            fetchMechanicsForModal(this.value, currentMechId, apptId);
        });
    }

    function fetchMechanicsForModal(dateStr, preselectMechId, apptId) {
        if (!dateStr || !editMechanicSelect) return;

        fetch(`api/get_mechanics.php?date=${encodeURIComponent(dateStr)}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                editMechanicSelect.innerHTML = '<option value="">-- Select Senior Mechanic --</option>';

                data.mechanics.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.id;

                    // If it's this appointment's current mechanic on this same date, count includes itself so slots_left is effectively +1
                    const isCurrentAssigned = (m.id == preselectMechId);
                    const effectiveSlots = isCurrentAssigned ? (m.slots_left + (m.booked_count > 0 ? 1 : 0)) : m.slots_left;
                    const isOccupied = (!isCurrentAssigned && m.is_occupied);

                    let statusText = `${m.slots_left} of ${m.max_daily_slots} slots available`;
                    if (isOccupied) {
                        statusText = `FULLY OCCUPIED (4/4)`;
                        opt.disabled = true;
                    } else if (isCurrentAssigned) {
                        statusText = `Currently Assigned (${m.booked_count}/${m.max_daily_slots})`;
                    }

                    opt.textContent = `${m.name} — ${m.specialty} [${statusText}]`;

                    if (m.id == preselectMechId) {
                        opt.selected = true;
                    }

                    editMechanicSelect.appendChild(opt);
                });

                if (editSlotNotice) {
                    editSlotNotice.style.display = 'block';
                    editSlotNotice.textContent = `Showing senior mechanic workload and slot availability for ${dateStr}. Maximum 4 cars/day.`;
                }
            })
            .catch(err => console.error(err));
    }

    if (editModalClose) {
        editModalClose.addEventListener('click', () => editModal.classList.remove('active'));
    }

    // 3. Submit Edit Form
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const saveBtn = document.getElementById('saveEditBtn');
            const originalText = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = 'Saving Changes...';

            const formData = new FormData(editForm);

            fetch('api/update_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;

                if (data.success) {
                    alert('✓ ' + data.message);
                    window.location.reload();
                } else {
                    if (editAlert) {
                        editAlert.className = 'alert alert-danger';
                        editAlert.innerHTML = `<span class="alert-icon">✕</span><div>${data.message}</div>`;
                        editAlert.style.display = 'flex';
                    } else {
                        alert('Error: ' + data.message);
                    }
                }
            })
            .catch(err => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
                alert('Network communication error with server.');
                console.error(err);
            });
        });
    }

    // 4. Cancel / Delete Action
    document.querySelectorAll('.btn-cancel-appointment').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const client = this.getAttribute('data-client');

            if (!confirm(`Are you sure you want to cancel the appointment #${id} for ${client}?\nThis will immediately free up the mechanic slot.`)) {
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', 'cancel');

            fetch('api/delete_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✓ ' + data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Could not cancel appointment.'));
                }
            })
            .catch(err => {
                alert('Network communication error.');
                console.error(err);
            });
        });
    });

    // 5. Permanent Delete Action
    document.querySelectorAll('.btn-delete-appointment').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const client = this.getAttribute('data-client');

            if (!confirm(`CAUTION: Permanently delete record for Appointment #${id} (${client})?\nThis cannot be undone.`)) {
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('action', 'delete');

            fetch('api/delete_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✓ ' + data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Could not delete record.'));
                }
            })
            .catch(err => {
                alert('Network communication error.');
                console.error(err);
            });
        });
    });

    // 6. View Slip Modal for Admin
    const viewSlipModal = document.getElementById('viewSlipModal');
    const viewSlipClose = document.getElementById('viewSlipClose');
    const printAdminSlipBtn = document.getElementById('printAdminSlipBtn');

    document.querySelectorAll('.btn-view-slip').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            fetch(`api/get_appointment.php?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return alert('Appointment not found');
                    const appt = data.appointment;
                    const content = document.getElementById('adminSlipContent');
                    content.innerHTML = `
                        <div class="ticket-box" id="printSlipArea">
                            <div class="ticket-header">
                                <h4>APEX MOTORS WORKSHOP</h4>
                                <p style="font-size: 0.8rem; color: #64748b;">Client Appointment Record</p>
                                <span class="ticket-badge">${appt.status.toUpperCase()}</span>
                            </div>
                            <div class="ticket-details">
                                <div class="ticket-row">
                                    <span class="label">Appointment ID</span>
                                    <span class="value">#${appt.id}</span>
                                </div>
                                <div class="ticket-row">
                                    <span class="label">Date</span>
                                    <span class="value" style="color: #d97706;">${appt.appointment_date}</span>
                                </div>
                                <div class="ticket-row">
                                    <span class="label">Assigned Mechanic</span>
                                    <span class="value">${escapeHtml(appt.mechanic_name)}</span>
                                </div>
                                <div class="ticket-row">
                                    <span class="label">Specialty</span>
                                    <span class="value">${escapeHtml(appt.mechanic_specialty)}</span>
                                </div>
                                <div class="ticket-row">
                                    <span class="label">Client Name</span>
                                    <span class="value">${escapeHtml(appt.client_name)}</span>
                                </div>
                                <div class="ticket-row">
                                    <span class="label">Phone</span>
                                    <span class="value">${escapeHtml(appt.phone)}</span>
                                </div>
                                <div class="ticket-row">
                                    <span class="label">Car Registration No.</span>
                                    <span class="value">${escapeHtml(appt.car_license)}</span>
                                </div>
                                <div class="ticket-row">
                                    <span class="label">Engine No.</span>
                                    <span class="value">${escapeHtml(appt.car_engine)}</span>
                                </div>
                                <div class="ticket-row" style="grid-column: span 2;">
                                    <span class="label">Address</span>
                                    <span class="value" style="font-weight: 500;">${escapeHtml(appt.address)}</span>
                                </div>
                                ${appt.admin_notes ? `
                                <div class="ticket-row" style="grid-column: span 2;">
                                    <span class="label">Manager Notes</span>
                                    <span class="value" style="color: #64748b; font-weight: normal;">${escapeHtml(appt.admin_notes)}</span>
                                </div>` : ''}
                            </div>
                        </div>
                    `;
                    viewSlipModal.classList.add('active');
                });
        });
    });

    if (viewSlipClose) {
        viewSlipClose.addEventListener('click', () => viewSlipModal.classList.remove('active'));
    }

    if (printAdminSlipBtn) {
        printAdminSlipBtn.addEventListener('click', () => window.print());
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
