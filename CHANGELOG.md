# Changelog

이 문서는 `cms-orbit/lms`의 릴리스 노트를 기록합니다.

## 4.0.0 - 2026-07-24

### 추가 (Phase 1 — Core LMS)

- 학습 도메인 모델: `Course`, `CourseSection`, `Lesson`, `Quiz`, `QuizQuestion`, `Enrollment`, `LessonProgress` (모두 `lms_` 프리픽스 테이블).
- Orbit 관리자 CRUD 엔티티: 코스/섹션/레슨/퀴즈/퀴즈문항/수강신청. `LmsServiceProvider` auto-discovery로 자동 등록되며 `Learning` 메뉴 섹션과 `lms.entities.*` 권한을 함께 등록합니다.
- 강사/수강생은 일반 Orbit 사용자를 참조(`config('lms.user_model')`)하므로 순정 라라벨 호스트에서도 동작합니다.
- 열거형: `CourseStatus`, `CourseLevel`, `LessonType`, `QuestionType`, `EnrollmentStatus`.
- `Enrollment::recalculateProgress()` — 레슨 완료 수를 코스 전체 레슨 수와 비교해 진도율을 산출하고, 100%가 되면 자동으로 완료 처리합니다.
- `QuizQuestion::isAnswerCorrect()` — 제출 답안을 정답 집합과 순서 무관 비교.
- 한국어 번역(`resources/lang/ko.json`), Boost 가이드라인.

> Phase 2(수익화), Phase 3(참여 기능), Phase 4(공개 프론트엔드 스캐폴딩)는 이후 릴리스에서 단계적으로 추가됩니다.
