# Virtual Classroom Database Migrations Summary

## Overview
This document provides a complete overview of all database migrations for the Virtual Classroom project. The migrations are organized chronologically and represent the complete database schema.

## Migration Files Created/Updated

### 1. **Users Table** (Existing - Updated)
- **File**: `0001_01_01_000000_create_users_table.php`
- **Columns**: 
  - id (Primary Key)
  - name, email, username (unique)
  - password, role (admin, instructor, student)
  - is_verified (boolean) - Added by 2025_11_28_000019

### 2. **Classes Table** (New)
- **File**: `2025_11_28_000001_create_classes_table.php`
- **Columns**:
  - id (Primary Key)
  - name, subject, description, schedule
  - teacher_id (Foreign Key → users.id)
  - timestamps

### 3. **Live Sessions Table** (New)
- **File**: `2025_11_28_000002_create_live_sessions_table.php`
- **Columns**:
  - id (Primary Key)
  - class_id (Foreign Key → classes.id)
  - title, description, session_number
  - scheduled_at, started_at, ended_at
  - meeting_url, recording_url
  - status (scheduled, ongoing, completed, cancelled)
  - timestamps

### 4. **Enrollments Table** (Updated)
- **File**: `2025_11_28_000003_update_enrollments_table.php`
- **Changes**:
  - Added class_id (Foreign Key → classes.id)
  - Renamed user_id to student_id
  - Updated status enum values
  - Kept course_id for backward compatibility

### 5. **Attendance Table** (New)
- **File**: `2025_11_28_000004_create_attendance_table.php`
- **Columns**:
  - id (Primary Key)
  - session_id (Foreign Key → live_sessions.id)
  - class_id (Foreign Key → classes.id)
  - student_id (Foreign Key → users.id)
  - date, timestamp
  - status (present, absent, late, excused)
  - Unique constraint on (session_id, student_id)

### 6. **Session Participants Table** (New)
- **File**: `2025_11_28_000005_create_session_participants_table.php`
- **Columns**:
  - id (Primary Key)
  - session_id (Foreign Key → live_sessions.id)
  - user_id (Foreign Key → users.id)
  - joined_at, left_at
  - duration (in seconds)
  - timestamps
  - Unique constraint on (session_id, user_id)

### 7. **Session Chat Table** (New)
- **File**: `2025_11_28_000006_create_session_chat_table.php`
- **Columns**:
  - id (Primary Key)
  - session_id (Foreign Key → live_sessions.id)
  - user_id (Foreign Key → users.id)
  - message (text)
  - timestamp, timestamps
  - Indexes on session_id, user_id

### 8. **Assignments Table** (New)
- **File**: `2025_11_28_000007_create_assignments_table.php`
- **Columns**:
  - id (Primary Key)
  - session_id (Foreign Key → live_sessions.id, nullable)
  - class_id (Foreign Key → classes.id)
  - title, description
  - due_date, max_score
  - created_by (Foreign Key → users.id)
  - timestamps

### 9. **Submissions Table** (New)
- **File**: `2025_11_28_000008_create_submissions_table.php`
- **Columns**:
  - id (Primary Key)
  - assignment_id (Foreign Key → assignments.id)
  - student_id (Foreign Key → users.id)
  - file_path, submitted_at
  - score, feedback
  - status (pending, submitted, graded, late)
  - timestamps

### 10. **Quizzes Table** (Updated)
- **File**: `2025_11_28_000009_update_quizzes_table.php`
- **Added Columns**:
  - session_id (Foreign Key → live_sessions.id, nullable)
  - class_id (Foreign Key → classes.id)
  - start_time, end_time
  - created_by (Foreign Key → users.id)

### 11. **Questions Table** (Updated)
- **File**: `2025_11_28_000010_update_questions_table.php`
- **Added Columns**:
  - question_type (varchar)
  - points (integer)
  - order (integer)

### 12. **Question Options Table** (New)
- **File**: `2025_11_28_000011_create_question_options_table.php`
- **Columns**:
  - id (Primary Key)
  - question_id (Foreign Key → questions.id)
  - option_text, is_correct, order
  - timestamps

### 13. **Quiz Attempts Table** (Updated)
- **File**: `2025_11_28_000012_update_quiz_attempts_table.php`
- **Added Columns**:
  - time_taken (integer, in seconds)

### 14. **Student Answers Table** (New)
- **File**: `2025_11_28_000013_create_student_answers_table.php`
- **Columns**:
  - id (Primary Key)
  - attempt_id (Foreign Key → quiz_attempts.id)
  - question_id (Foreign Key → questions.id)
  - answer_text, is_correct
  - points_earned
  - timestamps

### 15. **Study Groups Table** (New)
- **File**: `2025_11_28_000014_create_study_groups_table.php`
- **Columns**:
  - id (Primary Key)
  - name, description
  - class_id (Foreign Key → classes.id)
  - created_by (Foreign Key → users.id)
  - timestamps

### 16. **Group Members Table** (New)
- **File**: `2025_11_28_000015_create_group_members_table.php`
- **Columns**:
  - id (Primary Key)
  - group_id (Foreign Key → study_groups.id)
  - user_id (Foreign Key → users.id)
  - role (member, moderator, admin)
  - joined_at
  - timestamps
  - Unique constraint on (group_id, user_id)

### 17. **Group Messages Table** (New)
- **File**: `2025_11_28_000016_create_group_messages_table.php`
- **Columns**:
  - id (Primary Key)
  - group_id (Foreign Key → study_groups.id)
  - user_id (Foreign Key → users.id)
  - message (text)
  - timestamp, timestamps
  - Indexes on group_id, user_id

### 18. **Notifications Table** (New)
- **File**: `2025_11_28_000017_create_notifications_table.php`
- **Columns**:
  - id (Primary Key)
  - user_id (Foreign Key → users.id)
  - title, message
  - type (assignment, quiz, announcement, session, grade, message, other)
  - is_read (boolean)
  - timestamps
  - Indexes on user_id, is_read

### 19. **Announcements Table** (New)
- **File**: `2025_11_28_000018_create_announcements_table.php`
- **Columns**:
  - id (Primary Key)
  - class_id (Foreign Key → classes.id)
  - title, content
  - created_by (Foreign Key → users.id)
  - timestamps
  - Indexes on class_id, created_at

## Database Relationships

```
users (1) ─→ (many) classes (as teacher)
users (1) ─→ (many) enrollments
users (1) ─→ (many) attendance
users (1) ─→ (many) live_sessions (participates)
users (1) ─→ (many) assignments (creates)
users (1) ─→ (many) submissions
users (1) ─→ (many) study_groups (creates)
users (1) ─→ (many) group_members
users (1) ─→ (many) notifications
users (1) ─→ (many) announcements (creates)

classes (1) ─→ (many) enrollments
classes (1) ─→ (many) live_sessions
classes (1) ─→ (many) attendance
classes (1) ─→ (many) assignments
classes (1) ─→ (many) quizzes
classes (1) ─→ (many) study_groups
classes (1) ─→ (many) announcements

live_sessions (1) ─→ (many) attendance
live_sessions (1) ─→ (many) session_participants
live_sessions (1) ─→ (many) session_chat
live_sessions (1) ─→ (many) assignments
live_sessions (1) ─→ (many) quizzes

quizzes (1) ─→ (many) questions
quizzes (1) ─→ (many) quiz_attempts

questions (1) ─→ (many) question_options
questions (1) ─→ (many) student_answers

quiz_attempts (1) ─→ (many) student_answers

assignments (1) ─→ (many) submissions

study_groups (1) ─→ (many) group_members
study_groups (1) ─→ (many) group_messages
```

## Running the Migrations

To execute all migrations:

```bash
php artisan migrate
```

To migrate specific tables:

```bash
php artisan migrate --step
```

To rollback:

```bash
php artisan migrate:rollback
```

## Key Features

✅ Complete virtual classroom database schema
✅ Proper foreign key relationships with cascade deletes
✅ Comprehensive indexes for performance
✅ Unique constraints where needed
✅ Enumerated types for status fields
✅ Timestamps for audit trail
✅ Support for live sessions, attendance, assignments, quizzes, and study groups
✅ Notification and announcement systems
✅ Session chat and participant tracking

## Notes

- All foreign keys are set to cascade on delete for data consistency
- Indexes are added to frequently queried columns for better performance
- Timestamps (created_at, updated_at) are included in all tables for audit purposes
- Some columns are nullable to support optional features
- Unique constraints prevent duplicate entries where necessary
