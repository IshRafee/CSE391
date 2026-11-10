/**
 * Workshop Online Appointment System - Client JavaScript
 * Real-time AJAX communication, live slot calculation, and validation
 */

document.addEventListener('DOMContentLoaded', function () {
    const appointmentForm = document.getElementById('appointmentForm');
    const appointmentDateInput = document.getElementById('appointment_date');
    const mechanicsContainer = document.getElementById('mechanicsContainer');
    const mechanicsLoading = document.getElementById('mechanicsLoading');
    const formAlert = document.getElementById('formAlert');
    const clientCheckNotice = document.getElementById('clientCheckNotice');

    // Modal elements
    const successModal = document.getElementById('successModal');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const printSlipBtn = document.getElementById('printSlipBtn');
    const bookAnotherBtn = document.getElementById('bookAnotherBtn');

    // 1. Set minimum date to today
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayStr = `${yyyy}-${mm}-${dd}`;
    
    if (appointmentDateInput) {
        appointmentDateInput.min = todayStr;
        if (!appointmentDateInput.value) {
            appointmentDateInput.value = todayStr;
        }

        // Fetch mechanics for initial date
        fetchMechanics(appointmentDateInput.value);

        // Fetch mechanics whenever date changes
        appointmentDateInput.addEventListener('change', function () {
            validateDateField();
            fetchMechanics(this.value);
            checkClientDuplicate();
        });
    }

    // Real-time check when phone or car license changes
    const phoneInput = document.getElementById('phone');
    const carLicenseInput = document.getElementById('car_license');
    const carEngineInput = document.getElementById('car_engine');
    const clientNameInput = document.getElementById('client_name');
    const addressInput = document.getElementById('address');

    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            validatePhoneField();
            debouncedCheckDuplicate();
        });
    }

    if (carLicenseInput) {
        carLicenseInput.addEventListener('input', function () {
            validateLicenseField();
            debouncedCheckDuplicate();
        });
    }

    if (carEngineInput) {
        carEngineInput.addEventListener('input', validateEngineField);
    }

    if (clientNameInput) {
        clientNameInput.addEventListener('input', validateNameField);
    }

    if (addressInput) {
        addressInput.addEventListener('input', validateAddressField);
    }

    let debounceTimer = null;
    function debouncedCheckDuplicate() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(checkClientDuplicate, 400);
    }

    /**
     * Fetch all mechanics and real-time slot availability for selected date
     */
    function fetchMechanics(dateStr) {
        if (!dateStr) return;

        if (mechanicsLoading) mechanicsLoading.style.display = 'block';
        if (mechanicsContainer) mechanicsContainer.innerHTML = '';

        fetch(`api/get_mechanics.php?date=${encodeURIComponent(dateStr)}`)
            .then(res => res.json())
            .then(data => {
                if (mechanicsLoading) mechanicsLoading.style.display = 'none';

                if (!data.success) {
                    showFormAlert('danger', data.message || 'Failed to load mechanic availability.');
                    return;
                }

                renderMechanicsList(data.mechanics, data.date);
            })
            .catch(err => {
                if (mechanicsLoading) mechanicsLoading.style.display = 'none';
                showFormAlert('danger', 'Network error connecting to workshop server.');
                console.error(err);
            });
    }

    /**
     * Render mechanic cards with slot availability
     */
    function renderMechanicsList(mechanics, dateStr) {
        if (!mechanicsContainer) return;
        mechanicsContainer.innerHTML = '';

        if (!mechanics || mechanics.length === 0) {
            mechanicsContainer.innerHTML = '<p class="text-muted">No senior mechanics found.</p>';
            return;
        }

        const selectedRadioVal = document.querySelector('input[name="mechanic_id"]:checked')?.value;

        mechanics.forEach(m => {
            const card = document.createElement('label');
            card.className = `mechanic-card ${m.is_occupied ? 'is-full' : ''}`;
            card.id = `mech_card_${m.id}`;

            let badgeClass = 'available';
            let badgeText = `${m.slots_left} / ${m.max_daily_slots} Free Slots`;

            if (m.is_occupied) {
                badgeClass = 'full';
                badgeText = 'Fully Occupied (0 Left)';
            } else if (m.slots_left === 1) {
                badgeClass = 'low';
                badgeText = 'Only 1 Slot Left!';
            }

            const isChecked = (!m.is_occupied && selectedRadioVal == m.id);

            card.innerHTML = `
                <input type="radio" name="mechanic_id" value="${m.id}" ${m.is_occupied ? 'disabled' : ''} ${isChecked ? 'checked' : ''}>
                <div class="mechanic-header">
                    <div class="mechanic-avatar">${getInitials(m.name)}</div>
                    <div>
                        <div class="mechanic-name">${escapeHtml(m.name)}</div>
                        <div style="font-size: 0.75rem; color: #64748b;">${m.experience_years} Years Senior Experience</div>
                    </div>
                </div>
                <div class="mechanic-spec">${escapeHtml(m.specialty)}</div>
                <div class="slot-indicator">
                    <span class="slot-badge ${badgeClass}">
                        ${m.is_occupied ? '✕' : '●'} ${badgeText}
                    </span>
                    <span style="font-size: 0.75rem; color: #94a3b8;">Max 4 cars/day</span>
                </div>
            `;

            // Card click & select styling
            if (!m.is_occupied) {
                card.addEventListener('click', function () {
                    document.querySelectorAll('.mechanic-card').forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                    const radio = card.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                    clearFieldError('mechanic_id');
                });

                if (isChecked) {
                    card.classList.add('selected');
                }
            }

            mechanicsContainer.appendChild(card);
        });
    }

    /**
     * Real-time duplicate booking check
     */
    function checkClientDuplicate() {
        const phone = phoneInput ? phoneInput.value.trim() : '';
        const license = carLicenseInput ? carLicenseInput.value.trim() : '';
        const date = appointmentDateInput ? appointmentDateInput.value.trim() : '';

        if (!date || (!phone && !license)) {
            if (clientCheckNotice) clientCheckNotice.style.display = 'none';
            return;
        }

        fetch(`api/check_client_booking.php?date=${encodeURIComponent(date)}&phone=${encodeURIComponent(phone)}&car_license=${encodeURIComponent(license)}`)
            .then(res => res.json())
            .then(data => {
                if (data.has_booking) {
                    if (clientCheckNotice) {
                        clientCheckNotice.style.display = 'block';
                        clientCheckNotice.className = 'alert alert-warning';
                        clientCheckNotice.innerHTML = `
                            <span class="alert-icon">⚠️</span>
                            <div>
                                <strong>Existing Booking Found:</strong> ${escapeHtml(data.message)}
                                <div style="font-size: 0.8rem; margin-top: 0.25rem;">Workshop policy permits only 1 appointment per client/vehicle per day.</div>
                            </div>
                        `;
                    }
                } else {
                    if (clientCheckNotice) clientCheckNotice.style.display = 'none';
                }
            })
            .catch(() => {});
    }

    // Field Validators
    function validateNameField() {
        const val = clientNameInput.value.trim();
        if (!val) {
            setFieldError('client_name', 'Client name is required.');
            return false;
        }
        if (val.length < 2) {
            setFieldError('client_name', 'Client name must be at least 2 characters.');
            return false;
        }
        setFieldSuccess('client_name');
        return true;
    }

    function validateAddressField() {
        const val = addressInput.value.trim();
        if (!val) {
            setFieldError('address', 'Workshop address is required.');
            return false;
        }
        setFieldSuccess('address');
        return true;
    }

    function validatePhoneField() {
        const val = phoneInput.value.trim();
        if (!val) {
            setFieldError('phone', 'Phone number is required.');
            return false;
        }
        // Requirement: "phone (that it has only number in it)"
        if (!/^[0-9]+$/.test(val)) {
            setFieldError('phone', 'Phone must contain only numbers (digits 0-9).');
            return false;
        }
        if (val.length < 7 || val.length > 15) {
            setFieldError('phone', 'Phone number must be between 7 and 15 digits.');
            return false;
        }
        setFieldSuccess('phone');
        return true;
    }

    function validateLicenseField() {
        const val = carLicenseInput.value.trim();
        if (!val) {
            setFieldError('car_license', 'Car registration / license number is required.');
            return false;
        }
        setFieldSuccess('car_license');
        return true;
    }

    function validateEngineField() {
        const val = carEngineInput.value.trim();
        if (!val) {
            setFieldError('car_engine', 'Car engine number is required.');
            return false;
        }
        // Requirement: "car engine no. (that it has only number in it)"
        if (!/^[0-9]+$/.test(val)) {
            setFieldError('car_engine', 'Engine number must contain only numbers (digits 0-9).');
            return false;
        }
        setFieldSuccess('car_engine');
        return true;
    }

    function validateDateField() {
        const val = appointmentDateInput.value.trim();
        if (!val) {
            setFieldError('appointment_date', 'Appointment date is required.');
            return false;
        }
        // Must only be valid date and not in past
        const dateObj = new Date(val + 'T00:00:00');
        if (isNaN(dateObj.getTime())) {
            setFieldError('appointment_date', 'Invalid date format.');
            return false;
        }
        const todayMid = new Date(todayStr + 'T00:00:00');
        if (dateObj < todayMid) {
            setFieldError('appointment_date', 'Appointment date cannot be in the past.');
            return false;
        }
        setFieldSuccess('appointment_date');
        return true;
    }

    function validateMechanicField() {
        const selected = document.querySelector('input[name="mechanic_id"]:checked');
        if (!selected) {
            setFieldError('mechanic_id', 'Please select a senior mechanic from the available list.');
            return false;
        }
        clearFieldError('mechanic_id');
        return true;
    }

    function setFieldError(fieldId, msg) {
        const field = document.getElementById(fieldId);
        const feedback = document.getElementById(fieldId + '_feedback');
        if (field) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        }
        if (feedback) {
            feedback.innerText = msg;
            feedback.style.display = 'block';
        }
    }

    function setFieldSuccess(fieldId) {
        const field = document.getElementById(fieldId);
        const feedback = document.getElementById(fieldId + '_feedback');
        if (field) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
        if (feedback) {
            feedback.style.display = 'none';
        }
    }

    function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        const feedback = document.getElementById(fieldId + '_feedback');
        if (field) field.classList.remove('is-invalid');
        if (feedback) feedback.style.display = 'none';
    }

    function showFormAlert(type, message) {
        if (!formAlert) return;
        formAlert.className = `alert alert-${type}`;
        const icon = type === 'success' ? '✓' : (type === 'danger' ? '✕' : 'ℹ');
        formAlert.innerHTML = `<span class="alert-icon">${icon}</span><div>${message}</div>`;
        formAlert.style.display = 'flex';
        formAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function clearFormAlert() {
        if (!formAlert) return;
        formAlert.style.display = 'none';
    }

    // 2. Form Submission Handler
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearFormAlert();

            const isValidName = validateNameField();
            const isValidAddress = validateAddressField();
            const isValidPhone = validatePhoneField();
            const isValidLicense = validateLicenseField();
            const isValidEngine = validateEngineField();
            const isValidDate = validateDateField();
            const isValidMechanic = validateMechanicField();

            if (!isValidName || !isValidAddress || !isValidPhone || !isValidLicense || !isValidEngine || !isValidDate || !isValidMechanic) {
                showFormAlert('danger', 'Please correct the highlighted errors in the form before submitting.');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Confirm Appointment';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Communicating with Server...';
            }

            const formData = new FormData(appointmentForm);

            fetch('api/book_appointment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }

                if (data.success) {
                    // Refresh mechanic slots to reflect new booking
                    fetchMechanics(appointmentDateInput.value);

                    // Show Confirmation Modal
                    renderSuccessModal(data.appointment);
                    appointmentForm.reset();
                    // Reset date to today and re-validate
                    appointmentDateInput.value = todayStr;
                    document.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
                    document.querySelectorAll('.mechanic-card').forEach(c => c.classList.remove('selected'));
                    if (clientCheckNotice) clientCheckNotice.style.display = 'none';
                } else {
                    // Requirement specific error notices
                    if (data.code === 'ALREADY_BOOKED') {
                        showFormAlert('warning', `<strong>Appointment Not Allowed:</strong> ${data.message}`);
                    } else if (data.code === 'MECHANIC_OCCUPIED') {
                        showFormAlert('danger', `<strong>Mechanic Occupied:</strong> ${data.message}`);
                        // Refresh to show updated slot list
                        fetchMechanics(appointmentDateInput.value);
                    } else if (data.errors) {
                        // Display per-field errors from server
                        for (const [fId, msg] of Object.entries(data.errors)) {
                            setFieldError(fId, msg);
                        }
                        showFormAlert('danger', data.message || 'Validation error.');
                    } else {
                        showFormAlert('danger', data.message || 'Unable to book appointment.');
                    }
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
                showFormAlert('danger', 'Network communication error with server. Please try again.');
                console.error(err);
            });
        });
    }

    /**
     * Show Confirmation Ticket Modal
     */
    function renderSuccessModal(appt) {
        const ticketArea = document.getElementById('modalTicketContent');
        if (!ticketArea || !successModal) return;

        ticketArea.innerHTML = `
            <div class="ticket-box" id="printSlipArea">
                <div class="ticket-header">
                    <h4>APEX MOTORS WORKSHOP</h4>
                    <p style="font-size: 0.8rem; color: #64748b;">Official Appointment Confirmation Slip</p>
                    <span class="ticket-badge">APPROVED & CONFIRMED</span>
                </div>
                <div class="ticket-details">
                    <div class="ticket-row">
                        <span class="label">Appointment ID</span>
                        <span class="value">#${appt.id}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="label">Appointment Date</span>
                        <span class="value" style="color: #d97706;">${appt.appointment_date}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="label">Assigned Senior Mechanic</span>
                        <span class="value">${escapeHtml(appt.mechanic_name)}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="label">Specialty</span>
                        <span class="value" style="font-size: 0.82rem;">${escapeHtml(appt.mechanic_specialty || '')}</span>
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
                        <span class="label">Car Engine No.</span>
                        <span class="value">${escapeHtml(appt.car_engine)}</span>
                    </div>
                    <div class="ticket-row" style="grid-column: span 2;">
                        <span class="label">Client Address</span>
                        <span class="value" style="font-weight: 500;">${escapeHtml(appt.address)}</span>
                    </div>
                </div>
                <div style="margin-top: 1.25rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0; font-size: 0.78rem; color: #64748b; text-align: center;">
                    Please present this appointment slip or ID #${appt.id} upon vehicle arrival. Maximum 4 cars are serviced per mechanic daily.
                </div>
            </div>
        `;

        successModal.classList.add('active');
    }

    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', () => successModal.classList.remove('active'));
    }

    if (bookAnotherBtn) {
        bookAnotherBtn.addEventListener('click', () => successModal.classList.remove('active'));
    }

    if (printSlipBtn) {
        printSlipBtn.addEventListener('click', function () {
            window.print();
        });
    }

    function getInitials(name) {
        if (!name) return 'ME';
        return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
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
