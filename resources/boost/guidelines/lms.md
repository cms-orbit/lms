# CMS Orbit LMS

`cms-orbit/lms` adds a TutorLMS-style learning management domain to the Orbit
admin engine. Phase 1 (Core LMS) ships admin CRUD for the learning structure.

## Domain model

- `Course` (`lms_courses`) — title, slug, instructor (user), level, status, category, duration.
- `CourseSection` (`lms_course_sections`) — ordered grouping ("topic") inside a course.
- `Lesson` (`lms_lessons`) — video or text unit inside a section; `is_preview` for free samples.
- `Quiz` (`lms_quizzes`) + `QuizQuestion` (`lms_quiz_questions`) — single/multiple/true-false questions with JSON `options`/`correct`.
- `Enrollment` (`lms_enrollments`) — a student's registration in a course; `progress` + status.
- `LessonProgress` (`lms_lesson_progress`) — per-lesson completion driving `Enrollment::recalculateProgress()`.

## Conventions

- All tables are `lms_`-prefixed to avoid collisions on a shared host database.
- Instructors and students are regular users; the model class comes from `config('lms.user_model')`.
- Each model has an Orbit `Entity` under `src/Entities`, registered automatically by `LmsServiceProvider` (auto-discovered). Permissions live under `lms.entities.{uriKey}.*`; all entities share the `lms` menu section ("Learning").
- Enums (`CourseStatus`, `CourseLevel`, `LessonType`, `QuestionType`, `EnrollmentStatus`) expose `label()` and static `options()`.

## Install-only

Backend self-registers on `composer require cms-orbit/lms` (entities, migrations,
menu, permissions). Run `php artisan migrate` to create the tables. No host file
edits are required.
