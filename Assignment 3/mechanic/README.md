# Apex Motors Workshop — Online Appointment System

A full-stack web application built using **HTML5, CSS3, JavaScript, PHP 8 and MySQL** to solve the car workshop mechanic allocation and queuing problem.

---

## 📌 Problem Scenario & Solution

- **The Problem:** The workshop employs 5 senior mechanics. Each mechanic is permitted a maximum of 4 active cars (clients) each day. When a senior mechanic reached capacity, the manager previously reassigned subsequent clients to any other available mechanic, preventing clients from receiving service from their desired specialist and creating chaotic scheduling.
- **The Solution:** An online appointment booking system where clients select their desired senior mechanic directly, view real-time remaining slot capacity, and lock in their appointment before arriving physically.

---

## 🌟 Key Features & Requirements Matrix

### Part 1 (70 Points)
1. **User Panel (`index.php`):**
   - Collects: **Client Name, Address, Phone, Car License / Registration Number, Car Engine Number, Appointment Date**, and **Mechanic Selection**.
   - **Real-Time Client-Server Communication:**
     - Selecting a date triggers immediate AJAX requests (`api/get_mechanics.php?date=...`) querying the server to compute each mechanic's available slots (`max 4 - booked`).
     - Live badges display exact availability (e.g. `3 / 4 Free Slots`, `Only 1 Slot Left!`, or `Fully Occupied`).
   - **Duplicate Appointment Validation:**
     - Upon submission, verifies whether the client (phone number or car registration) has already booked an appointment on that date.
     - If so, alerts the client: *"Appointment Rejected: You have already taken an appointment on that specific date."*
   - **Capacity Limit Enforcement:**
     - If a mechanic has reached the 4-car limit, the system notifies the client and prevents overbooking; otherwise, it approves the appointment.
   - **Help Facilities:**
     - Integrated help cards and FAQ on `index.php`, plus a dedicated Help & Rules page (`help.php`).
   - **Appointment Confirmation Slip:**
     - Generates printable confirmation receipt with unique Ticket ID, assigned mechanic, and client details.

2. **Admin Panel (`admin.php`):**
   - Protected dashboard with authentication (`admin_login.php`). Default credentials: `admin` / `admin123`.
   - **Appointment List Table:** Shows **Client Name, Phone, Car Registration Number, Car Engine Number, Appointment Date, Assigned Mechanic, and Status**.
   - **Reschedule & Reassign Mechanic:**
     - Admin can modify the appointment date and reassign to any other available senior mechanic.
     - The modal dynamically verifies mechanic slot availability on the target date so the admin cannot accidentally overbook a specialist.
   - **Slot Release on Cancellation:** Cancelling an appointment automatically frees up the mechanic's slot.

### Part 2 (30 Points)
- **Design & UX:**
  - Modern, responsive automotive workshop aesthetic (dark slate header, amber racing accents, clean cards, modal dialogs, status badges).
- **Client & Server-Side Validations:**
  - **Phone:** Digits only (validated in JavaScript regex and PHP `preg_match('/^[0-9]+$/')`).
  - **Car Engine Number:** Digits only (validated in JS and PHP).
  - **Appointment Date:** Valid date format, restricted to current and future dates.
  - **Mechanic Selection:** Must select a senior mechanic with free slots.
  - **Client Name & Address:** Non-empty, sanitized against XSS.
  - **SQL Injection Prevention:** 100% PDO prepared statements.

---

## 🗄️ Database Structure (`workshop_db`)

The database consists of 3 core tables:
1. `mechanics`:
   - Stores the 5 senior specialists with specialties, years of experience, and daily capacity (`max_daily_slots = 4`).
2. `appointments`:
   - Stores client and vehicle details (`client_name`, `address`, `phone`, `car_license`, `car_engine`, `appointment_date`, `mechanic_id`, `status`, `admin_notes`).
3. `admin_users`:
   - Stores administrator credentials with bcrypt password hashing.

---

## 🚀 Running the Project

### Option A: Using XAMPP (Recommended)
1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. The project directory is available at:
   - Primary: `C:\Users\ishti\.gemini\antigravity\scratch\workshop_appointment`
   - Linked to XAMPP: `C:\xampp\htdocs\workshop_appointment`
3. Open your browser:
   - Client Booking Portal: [http://localhost/workshop_appointment/](http://localhost/workshop_appointment/)
   - Help & Rules Guide: [http://localhost/workshop_appointment/help.php](http://localhost/workshop_appointment/help.php)
   - Admin Management Panel: [http://localhost/workshop_appointment/admin.php](http://localhost/workshop_appointment/admin.php)

### Option B: Using PHP Built-in Server
Open PowerShell in the project directory and run:
```powershell
& "C:\xampp\php\php.exe" -S localhost:8000
```
Then visit [http://localhost:8000](http://localhost:8000).

---

## 🔑 Admin Credentials
- **Username:** `admin`
- **Password:** `admin123`
