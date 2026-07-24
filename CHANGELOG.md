# Changelog

이 문서는 `cms-orbit/lms`의 릴리스 노트를 기록합니다.

## 4.1.0 - 2026-07-24

### 추가 (Phase 2 — 오픈마켓형 수익화)

- 코스 가격 필드(`is_free`, `price`, `sale_price`, `currency`, `commission_rate`)를 `lms_courses`에 추가.
- 마켓플레이스 모델: `Coupon`, `Order`, `OrderItem`, `Earning`, `Payout` + 마이그레이션/팩토리/열거형(`OrderStatus`, `CouponType`, `EarningStatus`, `PayoutStatus`).
- `CheckoutService` — 주문 생성 → 강사/플랫폼 수익 분배(커미션) → 결제 시 수강 등록. 무료 코스 즉시 등록, 쿠폰 할인, 환불(수익 회수·수강 취소) 지원.
- 관리자 엔티티: Orders / Coupons / Earnings / Payouts (`Marketplace` 섹션). 커미션 기본값은 `lms.marketplace.commission_rate`(기본 80%).

### 추가 (Phase 3 — 참여 기능)

- `Review`(별점·승인), `CourseQuestion`/`CourseAnswer`(코스 Q&A), `Assignment`/`AssignmentSubmission`(과제·채점), `Certificate`(수료증, 시리얼 자동 발급).
- 관리자 엔티티: Reviews / Q&A / Assignments / Certificates (`Engagement` 섹션).
- `Course::averageRating()` / `reviewsCount()`.

### 추가 (Phase 4 — 공개 프론트엔드 스캐폴딩)

- `php artisan lms:install-frontend` — 코스 카탈로그·상세·강의 플레이어·체크아웃·학생 대시보드 페이지(React/Inertia)와 공개 컨트롤러·라우트를 **호스트로 게시**합니다. 라라벨 스타터킷처럼 게시 후에는 호스트가 소유·자유롭게 수정합니다(`--force`로 덮어쓰기). `routes/lms.php`를 `routes/web.php`에 1회 등록합니다.

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
