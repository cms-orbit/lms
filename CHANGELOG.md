# Changelog

이 문서는 `cms-orbit/lms`의 릴리스 노트를 기록합니다.

## 4.4.0 - 2026-08-31

### 변경

- **php 제약 `^8.3` → `^8.4`**: php 8.3 환경에서는 더 이상 설치되지 않습니다.
- **`cms-orbit/core` `^4.1` → `^4.4`**.

### 안내

- **생산 의존은 하나도 바뀌지 않았습니다.** php 하한 상향으로 새로 받게 된 패키지는 Pest 5 계열(`require-dev`)뿐입니다. cms-orbit 전 패키지의 직접 의존 20개를 최신판과 전수 대조했고, 나머지 18개는 이미 php `^8.3` 에서 최신을 받고 있었습니다. 이번 상향은 기능 확보가 아니라 장기 정리 목적입니다.
- **소비자의 Laravel 11·12 지원은 유지됩니다** (`laravel/framework ^11.0 || ^12.0 || ^13.0`).

## 4.3.1 - 2026-08-28

### 수정

- **php 제약 `^8.2` → `^8.3` 복구**: 게시된 태그는 모두 `^8.3`이었으나 main에서 `^8.2`로 내려가 있었습니다. Laravel 13은 php `^8.3`을 요구하므로 `php ^8.2` + `laravel/framework ^13` 조합은 php 8.2 환경에서 조용히 Laravel 11을 설치합니다.
- **`laravel/pint` `^1.14` → `^1.30`** (1.30이 php `^8.3`을 요구).

### 추가

- **릴리스 파이프라인 도입**: `.githooks/pre-push`가 composer.json의 `version` 필드와 태그명이 어긋난 태그의 푸시를 차단합니다. `cms-orbit/core`의 `4.0.8` 태그가 `version: 4.0.7`로 만들어져 Packagist가 아무 오류 없이 그 태그를 무시했고, 4.0.8이 게시되지 않은 사실을 아무도 알지 못한 사고가 있었습니다. `bin/release <버전>`이 version 갱신·검증·커밋·태그·푸시를 한 동작으로 묶어 드리프트를 원천 차단하고, `cms-orbit/*` 의존이 실제로 Packagist에 게시되어 있는지 Composer 리졸버로 확인합니다. 클론 후 `composer install` 시 `core.hooksPath`가 자동 설정됩니다.

## 4.3.0 - 2026-07-24

### 변경 (이수증 빌더를 관리자로 이관)

- **이수증 GUI 빌더를 Orbit 관리자 엔티티로 구성**했습니다. `CertificateTemplateEntity`의 편집 화면에 커스텀 `ReactField`(`lms-certificate-builder`)를 추가해, 관리자에서 캔버스에 텍스트/플레이스홀더를 드래그 배치합니다. 컴포넌트는 패키지 `resources/js`가 소유하고 `frontend.json`의 `registrations`로 **자동 등록**됩니다(`orbit:frontend-sync` 필요, 호스트 파일 수동 편집 불필요 — `cms-orbit/core ^4.0.12`).
- 공개 스토어프론트에 있던 빌더 페이지/컨트롤러/라우트를 제거했습니다. 학생용 이수증 **보기/인쇄**(`/certificates/{id}`)는 스토어프론트에 유지됩니다.
- **자립성**: 패키지의 관리자·백엔드 기능은 게시된 스토어프론트 스캐폴딩과 무관하게 동작합니다. 호스트에서 스토어프론트 게시물을 삭제해도 관리자 CRUD·엔티티·이수증 빌더는 그대로 작동합니다.

## 4.2.0 - 2026-07-24

### 추가 (드립 콘텐츠 + 플레이어 옵션)

- 코스 드립 콘텐츠: `drip_enabled` + `drip_type`(`off`/`after_days`/`by_date`/`sequential`/`prerequisite`), 레슨별 `drip_days`·`drip_date`·`drip_prerequisite_id`.
- `Lesson::isUnlockedFor(Enrollment)` / `unlockDateFor()` — 드립 규칙에 따른 레슨 잠금 판정(무료 미리보기는 항상 열림).
- 코스 플레이어 옵션: `player_disable_seek`, `player_disable_fastforward`, `player_autoplay`, `player_require_completion` + `Course::playerSettings()`.

### 추가 (이수증 템플릿 + 빌더)

- `CertificateTemplate`(이름·방향·크기·배경·요소 JSON·기본값) + `CertificateTemplateEntity` 관리자 CRUD.
- `CertificateService` — 완료 수강에 이수증 발급(멱등), `{{student_name}}`·`{{course_title}}`·`{{instructor_name}}`·`{{issued_date}}`·`{{serial}}` 치환으로 HTML 렌더(인쇄/PDF용).
- 발급 이수증을 템플릿과 연결(`certificate_template_id`).

### 추가 (강사 대시보드 집계)

- `InstructorDashboardService` — 강사별 코스/수강생/수강수/매출(총·정산가능·정산완료)·최근 리뷰·최근 Q&A 집계.

### 추가 (공개 프론트엔드 스캐폴딩 확장, `lms:install-frontend`)

- **강사 대시보드**(`/instructor`), **이수증 GUI 빌더**(`/certificate-builder` — 캔버스에서 텍스트/플레이스홀더를 드래그 배치·저장), **이수증 보기/인쇄**(`/certificates/{id}`).
- **강의 플레이어 고도화**: 드립 잠금 표시, 옵션에 따른 **빨리감기/탐색 불가**(네이티브 비디오 기준) 적용.

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
