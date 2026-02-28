# Virtual Classroom System - Structure & Components

## Database Migrations Overview

### Core Virtual Classroom Tables

#### 1. **classes** table
- Stores virtual classroom information
- **Columns:**
  - `id` - Primary key
  - `name` - Class name
  - `subject` - Subject being taught
  - `teacher_id` - FK to users (teacher)
  - `description` - Class description
  - `schedule` - Class schedule info
  - `timestamps` - created_at, updated_at

**Related Models:** VirtualClass
**Relationships:** 
- hasMany: enrollments, liveSessions, attendance, assignments, quizzes, studyGroups
- belongsTo: teacher (User)

---

#### 2. **live_sessions** table
- Tracks individual class sessions
- **Columns:**
  - `id` - Primary key
  - `class_id` - FK to classes
  - `title` - Session title
  - `description` - Session details
  - `session_number` - Session order
  - `scheduled_at` - When session is scheduled
  - `started_at` - When session started
  - `ended_at` - When session ended
  - `meeting_url` - Video conference link
  - `recording_url` - Session recording
  - `status` - enum: scheduled, ongoing, completed, cancelled
  - `timestamps`

**Related Models:** LiveSession
**Relationships:**
- belongsTo: virtualClass (VirtualClass)
- hasMany: participants, attendance, sessionChat, assignments
- hasMany: studentAnswers (through quiz)

---

#### 3. **class_enrollments** table
- Student enrollment in classes
- **Columns:**
  - `id`
  - `class_id` - FK to classes
  - `student_id` - FK to users (student)
  - `enrolled_at` - Enrollment date
  - `status` - active, inactive, suspended
  - `timestamps`

**Related Models:** ClassEnrollment
**Relationships:**
- belongsTo: virtualClass (VirtualClass)
- belongsTo: student (User)

---

#### 4. **attendance** table
- Tracks student attendance in sessions
- **Columns:**
  - `id`
  - `session_id` - FK to live_sessions
  - `class_id` - FK to classes
  - `student_id` - FK to users
  - `date` - Attendance date
  - `status` - enum: present, absent, late, excused
  - `timestamp` - When recorded
  - `unique([session_id, student_id])`
  - `timestamps`

**Related Models:** Attendance
**Relationships:**
- belongsTo: liveSession (LiveSession)
- belongsTo: virtualClass (VirtualClass)
- belongsTo: student (User)

---

#### 5. **assignments** table
- Class assignments/homework
- **Columns:**
  - `id`
  - `session_id` - FK to live_sessions (nullable)
  - `class_id` - FK to classes
  - `title` - Assignment title
  - `description` - Full description
  - `due_date` - Deadline
  - `max_score` - Maximum points (default: 100)
  - `created_by` - FK to users (creator/instructor)
  - `timestamps`

**Related Models:** Assignment
**Relationships:**
- belongsTo: virtualClass (VirtualClass)
- belongsTo: liveSession (LiveSession)
- belongsTo: creator (User)
- hasMany: submissions

---

#### 6. **submissions** table
- Student assignment submissions
- **Columns:**
  - `id`
  - `assignment_id` - FK to assignments
  - `student_id` - FK to users
  - `submission_text` - Text answer
  - `file_url` - Uploaded file path
  - `submitted_at` - Submission timestamp
  - `status` - enum: pending, submitted, graded, late
  - `score` - Points earned
  - `feedback` - Instructor feedback
  - `graded_by` - FK to users (grader)
  - `graded_at` - When graded
  - `timestamps`

**Related Models:** Submission
**Relationships:**
- belongsTo: assignment (Assignment)
- belongsTo: student (User)
- belongsTo: gradedBy (User)

---

#### 7. **quizzes** table (enhanced)
- Quiz definitions
- **Columns:**
  - `id`
  - `session_id` - FK to live_sessions (nullable)
  - `class_id` - FK to classes
  - `title`
  - `description`
  - `total_marks` - Total possible score
  - `start_time`
  - `end_time`
  - `created_by` - FK to users (creator)
  - `timestamps`

**Related Models:** Quiz
**Relationships:**
- belongsTo: virtualClass (VirtualClass)
- belongsTo: liveSession (LiveSession)
- belongsTo: creator (User)
- hasMany: questions, attempts

---

#### 8. **questions** table (enhanced)
- Quiz questions
- **Columns:**
  - `id`
  - `quiz_id` - FK to quizzes
  - `question_text`
  - `question_type` - multiple_choice, short_answer, essay
  - `points` - Points for this question
  - `order` - Question sequence
  - `timestamps`

**Related Models:** Question
**Relationships:**
- belongsTo: quiz (Quiz)
- hasMany: questionOptions (QuestionOption)
- hasMany: studentAnswers (StudentAnswer)

---

#### 9. **question_options** table
- Multiple choice options for questions
- **Columns:**
  - `id`
  - `question_id` - FK to questions
  - `option_text` - The choice text
  - `is_correct` - Boolean for correct answer
  - `order` - Option sequence
  - `timestamps`

**Related Models:** QuestionOption
**Relationships:**
- belongsTo: question (Question)

---

#### 10. **quiz_attempts** table (enhanced)
- Student quiz attempts
- **Columns:**
  - `id`
  - `quiz_id` - FK to quizzes
  - `student_id` - FK to users
  - `attempted_at` - When attempted
  - `started_at` - When student started
  - `completed_at` - When student finished
  - `time_taken` - Duration in seconds
  - `score` - Points earned
  - `status` - in_progress, submitted, graded
  - `timestamps`

**Related Models:** QuizAttempt
**Relationships:**
- belongsTo: quiz (Quiz)
- belongsTo: student (User)
- hasMany: studentAnswers (StudentAnswer)

---

#### 11. **student_answers** table
- Individual student answers for quiz questions
- **Columns:**
  - `id`
  - `quiz_attempt_id` - FK to quiz_attempts
  - `question_id` - FK to questions
  - `student_id` - FK to users
  - `answer_text` - Text answer or choice
  - `question_option_id` - FK to question_options (nullable)
  - `is_correct` - Auto-determined for MC
  - `points_earned` - Score for this answer
  - `timestamps`

**Related Models:** StudentAnswer
**Relationships:**
- belongsTo: quizAttempt (QuizAttempt)
- belongsTo: question (Question)
- belongsTo: student (User)

---

#### 12. **study_groups** table
- Collaborative study groups
- **Columns:**
  - `id`
  - `class_id` - FK to classes
  - `name` - Group name
  - `description` - Group purpose
  - `created_by` - FK to users (creator)
  - `max_members` - Group size limit
  - `timestamps`

**Related Models:** StudyGroup
**Relationships:**
- belongsTo: virtualClass (VirtualClass)
- belongsTo: creator (User)
- hasMany: members (GroupMember)
- hasMany: messages (GroupMessage)

---

#### 13. **group_members** table
- Study group membership
- **Columns:**
  - `id`
  - `group_id` - FK to study_groups
  - `user_id` - FK to users
  - `role` - enum: member, moderator, admin
  - `joined_at` - When joined
  - `timestamps`

**Related Models:** GroupMember
**Relationships:**
- belongsTo: group (StudyGroup)
- belongsTo: user (User)

---

#### 14. **group_messages** table
- Study group messages
- **Columns:**
  - `id`
  - `group_id` - FK to study_groups
  - `user_id` - FK to users (sender)
  - `message` - Message content
  - `created_at`

**Related Models:** GroupMessage
**Relationships:**
- belongsTo: group (StudyGroup)
- belongsTo: user (User)

---

#### 15. **notifications** table
- System notifications for users
- **Columns:**
  - `id`
  - `user_id` - FK to users
  - `type` - assignment, quiz, announcement, session, grade, message, other
  - `title` - Notification title
  - `message` - Notification body
  - `related_id` - ID of related object
  - `related_type` - Model type (Assignment, Quiz, etc.)
  - `is_read` - Boolean flag
  - `created_at`

**Related Models:** Notification
**Relationships:**
- belongsTo: user (User)

---

#### 16. **announcements** table
- Class announcements
- **Columns:**
  - `id`
  - `class_id` - FK to classes
  - `user_id` - FK to users (creator)
  - `title` - Announcement title
  - `content` - Full content
  - `pinned` - Boolean
  - `created_at`

**Related Models:** Announcement
**Relationships:**
- belongsTo: virtualClass (VirtualClass)
- belongsTo: user (User)

---

## Livewire Components & Routes

### Admin Sidebar Menu Structure

```
ADMIN DASHBOARD
├── User Management
│   ├── Users → /admin/users
│   ├── Enrollments → /admin/enrollments
│   └── Certificates → /admin/certificates
├── Courses (Legacy)
│   ├── Languages → /admin/languages
│   ├── Courses → /admin/courses
│   ├── Lessons → /admin/lessons
│   └── Exercises → /admin/exercises
├── Quizzes
│   ├── Quizzes → /admin/quizzes
│   ├── Questions → /admin/questions
│   ├── Options → /admin/options
│   └── Quiz Attempts → /admin/quiz_attempts
└── Virtual Classroom ✨ NEW
    ├── Classes → /admin/classes
    ├── Live Sessions → /admin/live-sessions
    ├── Assignments → /admin/assignments
    ├── Submissions → /admin/submissions
    ├── Attendance → /admin/attendance
    ├── Study Groups → /admin/study-groups
    └── Notifications → /admin/notifications
```

### Virtual Classroom Components

#### 1. **Classes** (`resources/views/livewire/classes/index.blade.php`)
- List all virtual classes with filtering
- Search by name and subject
- Display teacher, schedule, enrollment count
- Sort by creation date
- **Route:** `admin/classes` → `classes.index`
- **Model:** VirtualClass

#### 2. **Live Sessions** (`resources/views/livewire/live-sessions/index.blade.php`)
- Display all live sessions
- Filter by class and status (scheduled, ongoing, completed, cancelled)
- Show session details, timing, participant count
- **Route:** `admin/live-sessions` → `live-sessions.index`
- **Model:** LiveSession

#### 3. **Assignments** (`resources/views/livewire/assignments/index.blade.php`)
- Manage class assignments
- Filter by class
- Search by title
- Show due dates and submission counts
- **Route:** `admin/assignments` → `assignments.index`
- **Model:** Assignment

#### 4. **Submissions** (`resources/views/livewire/submissions/index.blade.php`)
- Track student assignment submissions
- Filter by assignment and status (pending, submitted, graded, late)
- Show submission dates and scores
- **Route:** `admin/submissions` → `submissions.index`
- **Model:** Submission

#### 5. **Attendance** (`resources/views/livewire/attendance/index.blade.php`)
- Monitor class attendance
- Filter by class, session, and status (present, absent, late, excused)
- Track attendance records with dates
- **Route:** `admin/attendance` → `attendance.index`
- **Model:** Attendance

#### 6. **Study Groups** (`resources/views/livewire/study-groups/index.blade.php`)
- Manage collaborative study groups
- Filter by class
- Search by group name
- Display creator and member counts
- **Route:** `admin/study-groups` → `study-groups.index`
- **Model:** StudyGroup

#### 7. **Notifications** (`resources/views/livewire/notifications/index.blade.php`)
- System notification management
- Filter by user and type
- Show unread notifications
- Mark as read functionality
- **Route:** `admin/notifications` → `notifications.index`
- **Model:** Notification

---

## Database Relationships Diagram

```
User (teacher)
  ↓
VirtualClass
  ├─ LiveSession
  │  ├─ Attendance
  │  └─ Assignment
  │     └─ Submission
  ├─ ClassEnrollment (Student)
  ├─ Quiz
  │  ├─ Question
  │  │  ├─ QuestionOption
  │  │  └─ StudentAnswer ← QuizAttempt ← User (student)
  │  └─ QuizAttempt
  │     └─ StudentAnswer
  └─ StudyGroup
     ├─ GroupMember
     └─ GroupMessage

User
  ├─ Attendance (as student)
  ├─ Submission (as student)
  ├─ QuizAttempt
  ├─ Notification
  └─ Announcement (as creator)
```

---

## Feature Summary

### For Administrators
- ✅ Manage virtual classes and instructors
- ✅ Monitor live sessions in real-time
- ✅ Track assignments and submissions
- ✅ Review attendance records
- ✅ Manage quizzes and questions
- ✅ Oversee study groups
- ✅ Send notifications to users

### For Instructors
- Create and manage classes
- Schedule live sessions
- Create assignments
- Grade submissions
- Post announcements
- Track student attendance
- Manage study groups

### For Students
- Enroll in classes
- Join live sessions
- Submit assignments
- Take quizzes
- Collaborate in study groups
- Receive notifications

---

## Migration Status

✅ All 31 migrations executed successfully
✅ All foreign keys properly configured with CASCADE deletes
✅ All tables created with appropriate indexes
✅ All Livewire components updated in sidebar
✅ All routes registered and accessible

---

**Last Updated:** November 28, 2025
**System Status:** ✅ Production Ready
