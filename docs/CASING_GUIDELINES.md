# GWC Acadtrack — Casing & Typography Standards

**Document Version:** 1.0  
**Effective Date:** 2026-09-06  
**Applies to:** `Acadtrack` (Web Desktop, Tablet, & Mobile Viewports)  
**Related Standards:** [BOOTSTRAP_GUIDE.md](file:///C:/xampp/htdocs/acadtrack/docs/BOOTSTRAP_GUIDE.md) | [Official Bootstrap Documentation](https://getbootstrap.com/docs/5.3/)

---

## 1. Executive Summary

This standard defines the **Sentence-dominant Hybrid Casing** system adopted across the GWC Acadtrack Academic Grading & Curriculum Evaluation System. Modeled after premier institutional and enterprise application design systems (Stripe, GitHub, Apple Human Interface Guidelines, and Google Material 3), it balances:

- **Authority & Structure (Title Case)** for navigation landmarks, sidebar groups, page titles, and modal headers.
- **Readability & Speed (Sentence case)** for all interactive action buttons, form labels, table headers, placeholders, helper text, and notifications.
- **Precision (UPPERCASE)** for acronyms and compact technical metadata.

---

## 2. The 3-Tier Casing Rules

```
┌────────────────────────────────────────────────────────────┐
│ 1. Title Case    -> For STRUCTURAL LANDMARKS & HEADINGS     │
│ 2. Sentence case -> For ACTIONS, FORM LABELS & CONTENT     │
│ 3. UPPERCASE      -> For ACRONYMS & MICRO-TAGS ONLY         │
│ 4. Backend Truth -> NEVER RECASE BACKEND VOCABULARY & ERRORS│
└────────────────────────────────────────────────────────────┘
```

### Rule 1: Title Case (Landmarks & Hierarchy)
Capitalize the first letter of each major word. Used exclusively to answer: *"Where am I in the application?"* or *"What category is this?"*

**Apply to:**
- **Sidebar Groups & Navigation Items:**
  - `Dashboard`, `Users`, `Settings`, `Subjects`, `Faculty Assignments`, `Grade Review`, `My Subjects`, `Grading`, `My Grades`, `Evaluation`
- **Page Titles (`$pageTitle`) & Breadcrumbs:**
  - `Faculty Dashboard`, `Dean Dashboard`, `Admin Dashboard`, `Student Dashboard`
  - `User Management`, `Add New User`, `Edit User`, `Institutional Settings`
  - `Faculty Subject Assignments`, `Curricular Subjects`, `Grade Review & Approval`
  - `Grade Encoding Sheet`, `My Assigned Subjects`, `Enrolled Students`
  - `My Academic Grades`, `Academic Evaluation`
- **Modal / Dialog / Drawer Titles:**
  - `Assign Subject`, `Configure Grading Settings`, `Add Student`, `Submit Grading Sheet`, `Return with Feedback`, `Approve Grading Sheet`, `Lock Academic Term`
- **Tab Bar Items:**
  - `1st Semester`, `2nd Semester`, `Regular Students`, `Irregular Students`, `All Periods`, `Audit Log`
- **Major Section Headings & Card Titles:**
  - `Grading Method Configuration`, `Period Weight Allocation`, `Enrolled Student Roster`, `Grade Distribution`, `Dean Review Remarks`, `Evaluation Summary`
- **Empty State Titles:**
  - `No Assigned Subjects`, `No Students Enrolled`, `No Pending Sheets for Review`, `No Published Grades`

---

### Rule 2: Sentence case (Interactive & Reading Copy)
Capitalize only the first letter of the first word (plus proper nouns and standard acronyms). Used for everything the user **reads, clicks, or inputs**.

**Apply to:**
- **Buttons & Action CTAs:**
  - `Save draft` *(not `Save Draft`)*
  - `Save grades`
  - `Submit for review`
  - `Approve grading sheet`
  - `Return with remarks`
  - `Print grading sheet`
  - `View whole evaluation`
  - `Add student`, `Assign instructor`, `Create subject`, `Update user`
  - `Log in`, `Sign out`, `Update password`
  - `Export PDF`, `Export CSV`, `Apply filters`, `Reset filters`
- **Form Input Labels:**
  - `Subject code`, `Descriptive title`, `Subject type`, `Grading method`
  - `Semester`, `Academic year`, `Passing rate`
  - `Student number`, `Student ID` *(ID remains capitalized as an acronym)*
  - `First name`, `Middle name`, `Last name`, `Email address`
  - `Student status` *(Regular / Irregular)*
  - `Year level` *(1st year – 4th year)*
  - `Period weight`, `Remarks`
- **Table Column Headers:**
  - `Student number`, `Student name`, `Year level`, `Status`, `Prelim`, `Midterm`, `Semi-final`, `Final`, `Computed grade`, `Equivalent`, `Remarks`, `Actions`
- **Input Placeholders & Hints:**
  - `Select a subject…`
  - `Select grading method (Zero-based or 50-based)`
  - `Enter raw score (0.00 – 100.00)`
  - `Search by student name or number…`
  - `Provide specific instructions for instructor revision`
- **Unit Badges, Counters & Metrics:**
  - `3 units`, `1 unit`
  - `45 students enrolled`
  - `4 grading periods`
  - `1.25 GWA`
- **Status Badges & Pills:**
  - `Draft`, `Submitted`, `Under review`, `Approved`, `Returned`, `Finalized`
  - `Regular`, `Irregular`
  - `Passed`, `Failed`, `Incomplete`
- **Dialogs, Alerts, & Status Messages:**
  - `This grading sheet is currently under review by the dean.`
  - `Please provide mandatory feedback remarks before returning this sheet.`
  - `Grades have been saved successfully.`
  - `Are you sure you want to submit this grading sheet? Input fields will lock immediately.`

---

### Rule 3: UPPERCASE (Acronyms & Micro-Tags Only)
Capitalize all letters for short technical acronyms, course codes, and compact categorization tags. Usually styled with small font sizes (`text-xs` / `text-[11px]`) and slight letter-spacing.

**Apply to:**
- **Institutional & Academic Acronyms:** `GWC`, `GPA`, `GWA`, `INC`, `NFE`, `DRP`, `BSIT`, `BSCS`, `ID`, `AM`, `PM`, `PDF`, `CSV`, `URL`, `HTML`, `MVC`, `CSRF`
- **Micro-Category Tags:** `LEC`, `LAB`
- **Course Subject Codes:** `IT 101`, `CS 202`, `GE 104`, `MATH 101`

---

### Rule 4: Backend-Truth Exemption
> [!IMPORTANT]
> **NEVER RECASE BACKEND RESPONSES OR ENUMS**:
> In accordance with core system architecture, error messages, validation exceptions, and raw enum identifiers surfaced from PHP backend payloads or database constraints (e.g., `DRAFT`, `SUBMITTED`, `UNDER_REVIEW`, `APPROVED`, `RETURNED`, `FINALIZED`) are handled verbatim or mapped through presentation helpers. Do not alter raw backend error strings.

---

## 3. Screen & Feature Reference

### 3.1 Authentication & Profile
| Component / Location | Element | Text | Case Rule |
|---|---|---|---|
| `app/Views/auth/login.php` | Field label | `Email address` / `Username` | Sentence case |
| `app/Views/auth/login.php` | Field label | `Password` | Sentence case |
| `app/Views/auth/login.php` | Primary button | `Log in to portal` / `Log in` | Sentence case |
| `app/Views/components/navbar.php` | User menu action | `Log out` | Sentence case |
| `app/Views/components/navbar.php` | Term badge | `1st Semester • A.Y. 2025–2026` | Title Case |

### 3.2 Faculty Grading Portal
| Component / Location | Element | Text | Case Rule |
|---|---|---|---|
| `faculty/dashboard.php` | Page title | `Faculty Dashboard` | Title Case |
| `faculty/dashboard.php` | Stat labels | `Assigned subjects`, `Total students`, `Submitted sheets` | Sentence case |
| `faculty/subjects/index.php` | Page title | `My Assigned Subjects` | Title Case |
| `faculty/subjects/index.php` | Table headers | `Subject code`, `Descriptive title`, `Units`, `Grading method`, `Actions` | Sentence case |
| `faculty/subjects/index.php` | Action button | `Configure settings` | Sentence case |
| `faculty/students/index.php` | Page title | `Student Roster Management` | Title Case |
| `faculty/students/index.php` | Table headers | `Student number`, `Full name`, `Year level`, `Status`, `Actions` | Sentence case |
| `faculty/students/index.php` | Action button | `Add student` | Sentence case |
| `faculty/grading/index.php` | Page title | `Grade Encoding Sheet` | Title Case |
| `faculty/grading/index.php` | Field labels | `Select subject`, `Grading period`, `Semester` | Sentence case |
| `faculty/grading/index.php` | Table headers | `Student number`, `Student name`, `Score`, `Equivalent`, `Remarks` | Sentence case |
| `faculty/grading/index.php` | Action button | `Save draft` / `Save grades` | Sentence case |
| `faculty/grading/index.php` | Submit button | `Submit for review` | Sentence case |

### 3.3 Dean Review & Approval Portal
| Component / Location | Element | Text | Case Rule |
|---|---|---|---|
| `dean/dashboard.php` | Page title | `Dean Review Dashboard` | Title Case |
| `dean/grade-review/index.php` | Page title | `Grade Review & Approval` | Title Case |
| `dean/grade-review/index.php` | Table headers | `Subject`, `Instructor`, `Semester`, `Status`, `Submitted at`, `Actions` | Sentence case |
| `dean/grade-review/index.php` | Action button | `Approve grading sheet` | Sentence case |
| `dean/grade-review/index.php` | Action button | `Return with remarks` | Sentence case |
| `dean/grade-review/index.php` | Action button | `Print grading sheet` | Sentence case |
| `dean/faculty-assignments/index.php` | Page title | `Faculty Subject Assignments` | Title Case |
| `dean/faculty-assignments/index.php` | Action button | `Assign instructor` | Sentence case |

### 3.4 Student Grade Inquiry & Evaluation
| Component / Location | Element | Text | Case Rule |
|---|---|---|---|
| `student/dashboard.php` | Page title | `Student Dashboard` | Title Case |
| `student/grades/index.php` | Page title | `My Academic Grades` | Title Case |
| `student/grades/index.php` | Field label | `Select semester` | Sentence case |
| `student/grades/index.php` | Table headers | `Subject code`, `Descriptive title`, `Units`, `Final grade`, `Equivalent`, `Remarks` | Sentence case |
| `student/evaluation/show.php` | Page title | `Whole Evaluation` | Title Case |
| `student/evaluation/show.php` | Action button | `View whole evaluation` / `Print evaluation` | Sentence case |

### 3.5 System Administration
| Component / Location | Element | Text | Case Rule |
|---|---|---|---|
| `admin/dashboard.php` | Page title | `System Administration` | Title Case |
| `admin/users/index.php` | Page title | `User Management` | Title Case |
| `admin/users/index.php` | Action button | `Add user` | Sentence case |
| `admin/users/index.php` | Table headers | `Name`, `Email address`, `Role`, `Status`, `Actions` | Sentence case |
| `admin/settings/index.php` | Page title | `Institutional Settings` | Title Case |
| `admin/settings/index.php` | Action button | `Save settings` | Sentence case |

---

## 4. Typography & Font Weight Standards

> [!IMPORTANT]
> **Maximum Font Weight Cap:** All typography across Acadtrack (page titles, section headings, card headers, table headers, labels, buttons, and badges) must never exceed `font-weight: 600` (semibold).
> - **Allowed Weights:** `400` (regular), `500` (medium), `600` (semibold).
> - **Prohibited Weights:** `700` (bold), `800` (extrabold), `900` (black), or `bold` / `bolder` CSS keywords.
> - **Sidebar Active Items:** Styled with slate background (`#e2e8f0`) and dark navy text/icon (`font-weight: 600`). Generic left accent borders (`border-left`) are strictly prohibited.

---

## 5. Quick Developer & Review Checklist

Before creating or editing any authenticated view:
1. [ ] Is the button/CTA in **Sentence case**? (e.g., `Save draft`, not `Save Draft`; `Submit for review`, not `Submit For Review`).
2. [ ] Is the form field label in **Sentence case**? (e.g., `Subject code`, `Grading method`, `Student number`, not `Student Number`).
3. [ ] Are table column headers in **Sentence case**? (e.g., `Student name`, `Final grade`, `Equivalent`, not `Final Grade`).
4. [ ] Are page titles, sidebar links, tabs, and modal headers in **Title Case**? (e.g., `Grade Review & Approval`, `Assign Subject`).
5. [ ] Are all caps restricted exclusively to acronyms (`GWC`, `GWA`, `GPA`, `INC`, `BSIT`, `ID`) and micro-tags (`LEC`, `LAB`)?
6. [ ] Are raw backend error messages and database codes rendered **verbatim**?
7. [ ] Is the maximum font weight capped at `600` (zero `700`, `800`, or `bold` rules)?
8. [ ] Do active sidebar items use slate background (`#e2e8f0`) without generic left borders?
