Table users {
  id int [pk]
  username varchar
  email varchar
  password varchar
  role varchar
  is_verified boolean
  created_at datetime
}

Table classes {
  id int [pk]
  name varchar
  subject varchar
  teacher_id int [ref: > users.id]
  description text
  schedule varchar
  created_at datetime
}

Table enrollments {
  id int [pk]
  class_id int [ref: > classes.id]
  student_id int [ref: > users.id]
  enrolled_at datetime
  status varchar
}

Table attendance {
  id int [pk]
  session_id int [ref: > live_sessions.id]
  class_id int [ref: > classes.id]
  student_id int [ref: > users.id]
  date date
  status varchar
  timestamp datetime
}

Table live_sessions {
  id int [pk]
  class_id int [ref: > classes.id]
  title varchar
  description text
  session_number int
  scheduled_at datetime
  started_at datetime
  ended_at datetime
  meeting_url varchar
  recording_url varchar
  status varchar
}

Table session_participants {
  session_id int [ref: > live_sessions.id]
  user_id int [ref: > users.id]
  joined_at datetime
  left_at datetime
  duration int
}

Table session_chat {
  id int [pk]
  session_id int [ref: > live_sessions.id]
  user_id int [ref: > users.id]
  message text
  timestamp datetime
}

Table assignments {
  id int [pk]
  session_id int [ref: > live_sessions.id, null]
  class_id int [ref: > classes.id]
  title varchar
  description text
  due_date datetime
  max_score int
  created_by int [ref: > users.id]
  created_at datetime
}

Table submissions {
  id int [pk]
  assignment_id int [ref: > assignments.id]
  student_id int [ref: > users.id]
  file_path varchar
  submitted_at datetime
  score int
  feedback text
  status varchar
}

Table quizzes {
  id int [pk]
  session_id int [ref: > live_sessions.id, null]
  class_id int [ref: > classes.id]
  title varchar
  description text
  duration int
  total_marks int
  start_time datetime
  end_time datetime
  created_by int [ref: > users.id]
}

Table questions {
  id int [pk]
  quiz_id int [ref: > quizzes.id]
  question_text text
  question_type varchar
  points int
  correct_answer text
  order int
}

Table question_options {
  id int [pk]
  question_id int [ref: > questions.id]
  option_text text
  is_correct boolean
  order int
}

Table quiz_attempts {
  id int [pk]
  quiz_id int [ref: > quizzes.id]
  student_id int [ref: > users.id]
  score int
  started_at datetime
  completed_at datetime
  time_taken int
}

Table student_answers {
  id int [pk]
  attempt_id int [ref: > quiz_attempts.id]
  question_id int [ref: > questions.id]
  answer_text text
  is_correct boolean
  points_earned int
}


Table study_groups {
  id int [pk]
  name varchar
  description text
  class_id int [ref: > classes.id]
  created_by int [ref: > users.id]
  created_at datetime
}

Table group_members {
  id int [pk]
  group_id int [ref: > study_groups.id]
  user_id int [ref: > users.id]
  role varchar
  joined_at datetime
}

Table group_messages {
  id int [pk]
  group_id int [ref: > study_groups.id]
  user_id int [ref: > users.id]
  message text
  timestamp datetime
}



Table notifications {
  id int [pk]
  user_id int [ref: > users.id]
  title varchar
  message text
  type varchar
  is_read boolean
  created_at datetime
}

Table announcements {
  id int [pk]
  class_id int [ref: > classes.id]
  title varchar
  content text
  created_by int [ref: > users.id]
  created_at datetime
}
