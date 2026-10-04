# AGENTS.md

## Project Name
Examination Duty Management Portal

## Project Goal
Build a Laravel-based dashboard portal to manage examination duty for teaching and non-teaching staff.

The portal must allow an admin to upload staff lists, add or edit staff records, create examination duty entries, mark staff as exam secretary, and generate detailed reports by date range.

---

## Main Roles

### Admin
The admin can:

- Upload staff list
- Add new staff
- Edit staff details
- Activate or deactivate staff
- Create exam duty entry
- Select staff for duty
- Mark exam secretary
- View reports
- Export reports

### Staff
Staff users do not need login in the first version unless required later.

---

## Core Modules

## 1. Staff Management Module

### Purpose
Manage teaching and non-teaching staff records.

### Features
- Upload staff list from Excel or CSV
- Add staff manually
- Edit staff details
- Deactivate staff
- Search staff
- Filter staff by type, department, and status

### Staff Fields
- Staff Code (auto-generated on bulk upload or manual create, e.g. T001 / NT012)
- Staff Name
- Staff Type
  - Teaching
  - Non-teaching
- Department (optional)
- Mobile Number (optional)
- Email (optional)
- Status
  - Active
  - Inactive

### Upload Rules (Bulk CSV/Excel)
- Allow Excel or CSV upload (simplified 2-column format)
- Required columns: **Staff Name**, **Staff Type** (Teaching / Non-teaching)
- Staff Code is **automatically generated** (Txxx for Teaching, NTxxx for Non-teaching)
- All imported records are set to **Active** status
- Show preview before final import
- Validate required fields + auto-generated code uniqueness
- Show row-wise errors
- Do not import invalid rows
- Other fields (Department, Mobile, Email) remain blank after import and can be filled via individual edit

---

## 2. Exam Duty Entry Module

### Purpose
Create examination duty records for selected staff.

### Duty Entry Fields
- Exam Name
- Exam Date
- Exam Time
- Default Exam Hours
- Remarks

### Duty Entry Flow
1. Admin opens duty entry page.
2. Admin enters exam name.
3. Admin selects exam date.
4. Admin selects or enters exam time.
5. Admin enters default exam hours.
6. Active staff list appears.
7. Admin selects staff who gave duty.
8. Admin marks exam secretary where needed.
9. Admin saves the duty entry.

### Staff Selection Table
Each staff row must show:

- Staff Name
- Staff Type
- Department
- Duty Checkbox
- Exam Secretary Checkbox

### Duty Rules
- Only active staff must appear in the duty entry list.
- At least one staff must be selected before saving.
- Exam secretary checkbox must be separate from normal duty checkbox.
- A staff may be:
  - Normal duty only
  - Exam secretary only
  - Both normal duty and exam secretary
- Prevent duplicate duty entry for the same staff, exam name, date, and time.
- Duty hours should default from the main duty entry.
- Allow custom duty hours per staff only if required later.

---

## 3. Report Module

### Purpose
Generate detailed and summary reports for exam duties.

### Report Filters
- Exam Name
- Date From
- Date To
- Exam Time
- Staff Name
- Staff Type
- Department
- Duty Type
  - Normal Duty
  - Exam Secretary

### Detailed Report Columns
- Exam Name
- Exam Date
- Exam Time
- Staff Code
- Staff Name
- Staff Type
- Department
- Duty Hours
- Normal Duty Status
- Exam Secretary Status
- Remarks

### Summary Report Columns
- Staff Code
- Staff Name
- Staff Type
- Department
- Total Duties
- Total Duty Hours
- Total Exam Secretary Duties

### Export Options
- Excel
- PDF
- Print

---

## Suggested Database Design

## Table: staffs

```sql
CREATE TABLE staffs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_code VARCHAR(50) UNIQUE,
    name VARCHAR(150) NOT NULL,
    staff_type ENUM('Teaching', 'Non-teaching') NOT NULL,
    department VARCHAR(100),
    mobile VARCHAR(20),
    email VARCHAR(150),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

---

## Table: exam_duties

```sql
CREATE TABLE exam_duties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(150) NOT NULL,
    exam_date DATE NOT NULL,
    exam_time VARCHAR(100) NOT NULL,
    default_hours DECIMAL(5,2) DEFAULT 3.00,
    remarks TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

---

## Table: exam_duty_staffs

```sql
CREATE TABLE exam_duty_staffs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_duty_id INT NOT NULL,
    staff_id INT NOT NULL,
    is_duty TINYINT(1) DEFAULT 0,
    is_exam_secretary TINYINT(1) DEFAULT 0,
    duty_hours DECIMAL(5,2) DEFAULT 3.00,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (exam_duty_id) REFERENCES exam_duties(id),
    FOREIGN KEY (staff_id) REFERENCES staffs(id),

    UNIQUE KEY unique_exam_staff (exam_duty_id, staff_id)
);
```

---

## Recommended Pages

## 1. Dashboard
Show:

- Total staff
- Teaching staff count
- Non-teaching staff count
- Total duty entries
- Total exam secretary duties
- Recent duty entries

---

## 2. Staff Upload Page
Fields and actions:

- Upload Excel or CSV (only Staff Name + Staff Type columns required)
- Download sample file (2-column template)
- Staff Code is auto-generated during import
- All records forced Active on bulk import
- Preview uploaded data
- Import valid rows
- Show invalid row errors
- Optional fields can be added later by editing records

---

## 3. Staff List Page
Features:

- Search by name or staff code
- Filter by staff type
- Filter by department
- Filter by status
- Add staff button
- Edit button
- Deactivate button

---

## 4. Add/Edit Staff Page
Fields:

- Staff Code
- Staff Name
- Staff Type
- Department
- Mobile
- Email
- Status

---

## 5. Duty Entry Page
Fields:

- Exam Name
- Exam Date
- Exam Time
- Default Exam Hours
- Remarks

Staff table:

- Staff Code
- Staff Name
- Staff Type
- Department
- Duty checkbox
- Exam Secretary checkbox

Actions:

- Save duty
- Reset form

---

## 6. Reports Page
Filters:

- Exam name
- Date range
- Staff type
- Staff name
- Department
- Duty type

Actions:

- Search
- Export Excel
- Export PDF
- Print

---

## Validation Rules

### Staff Validation
- Staff name is required.
- Staff type is required.
- Staff code (auto-generated when omitted) must be unique.
- Email must be valid if entered.
- Mobile number must be valid if entered.
- Status must be Active or Inactive.

### Duty Entry Validation
- Exam name is required.
- Exam date is required.
- Exam time is required.
- Default exam hours is required.
- Default exam hours must be numeric.
- At least one staff must be selected.
- Duplicate duty entry for the same staff, exam name, date, and time is not allowed.

---

## Business Logic

### Duplicate Duty Check
Before saving duty, check whether the same staff already has a duty for:

- Same exam name
- Same exam date
- Same exam time

If yes, do not save duplicate record.

### Staff Status Logic
Inactive staff:

- Must not appear in duty entry page
- Must remain visible in old reports if they had previous duty

### Report Logic
Reports must use saved duty records.

Even if staff details are edited later, old duty reports should still show the current staff information unless a snapshot system is added later.

---

## Fixed Tech Stack

### Backend
- Laravel

### Dashboard
- Laravel dashboard
- Admin panel style layout
- Sidebar navigation
- Top bar
- Dashboard cards
- Table-based management pages

### Database
- MySQL

### Frontend
- Blade templates
- Bootstrap
- jQuery
- DataTables

### Import/Export
- Excel import library
- Excel export library
- PDF export library

---

## Future Features

These can be added later:

- Staff login
- Duty confirmation by staff
- SMS or email notification
- Duty rotation logic
- Auto duty assignment
- Avoid assigning same staff too often
- Department-wise duty balancing
- Exam-wise attendance sheet
- Signature sheet print
- Role-based access control

---

## Final Expected Result
The Laravel dashboard portal must allow the college or institution to manage exam duties smoothly.

The first version must include:

- Staff upload
- Staff add/edit
- Exam name entry
- Date and time based duty entry
- Staff selection
- Exam secretary marking
- Detailed reports
- Summary reports
- Excel export
- PDF export
- Print option
