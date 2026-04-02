# Edumac School Portal - Development Log & Changelog

This document serves as an ongoing log for major features, tasks, and system modifications built into the School Portal.

## [April 2026] Extracurriculars & Shared Wallet System

### 1. Shared Parent Wallet System
- **Database Schema Upgrades:** Altered the `wallet` table to support `parent_id` alongside `student_id`.
- **Intelligent Parent Pooling:** Rewrote the backend logic in `Wallet_model.php` to serve as a smart interceptor. Any deposits injected into a student's profile are automatically rolled up and tied to their parent’s shared wallet if a `parent_id` link exists. This perfectly handles families with multiple children operating from one financial pool.
- **Admin UI Overhaul:** Switched out the hardcoded wallet selector for a comprehensive split-dropdown enabling administrators to search dynamically for either a **Student** or a **Parent** directly.
- **Security Standards Implemented:** Upgraded pure HTML strings to utilize CodeIgniter's native `form_open()` mechanism to inject standard Cross-Site Request Forgery (CSRF) tokens ensuring no form exploitation.

### 2. Extracurricular Activities Module
- **Activity Pipeline:** Finalized the module for Administrators to define global clubs and events. Fixed continuous 403 Forbidden network errors blocking insertions by retrofitting the view with CSRF headers.
- **Student Enrollment:** Fully engineered the assignment pipeline linking students to activities. Added `assign()` backend logic inside `Extracurricular.php` alongside a new UI allowing schools to seamlessly assign users and view enrolled participants.

---

## [March 2026] School Standardization & HR Integration

### 1. Nigerian Assessment Standards (WAEC/NECO)
- **Report Card Adaptation:** Hardcoded structures into Edumac to accept and map CA/Exam splits and report them cleanly under local grading scales.
- **Psychomotor & Affective Domains:** Designed from scratch a behavioral tracking portal that logs and subsequently prints customized personality/behavioral outcomes on final student result summaries.

### 2. Staff Payroll - Task 2
- **Excel Bank Exportation:** Finished configuring the backend capability letting the school system export all payroll logic down to Excel CSV. Verified stable logic and verified subject copying configurations across staff lines.
