# CMS Orbit LMS

`cms-orbit/lms`는 Orbit 관리자 엔진 위에 TutorLMS 스타일의 학습 관리(LMS)
도메인을 더하는 패키지입니다. 코스 → 섹션 → 레슨/퀴즈 구조와 수강신청·진도
관리를 Orbit `Entity` 기반 관리자 화면으로 제공합니다.

> 이 패키지는 TutorLMS의 **기능/구조를 참고해 Orbit 규약으로 새로 구현**한 것으로,
> 원본 코드를 포함하지 않습니다.

## 단계별 로드맵

| 단계 | 내용 | 상태 |
| --- | --- | --- |
| **Phase 1 — Core LMS** | 코스/섹션/레슨/퀴즈/수강신청/진도, 강사·수강생 | ✅ 4.0.0 |
| **Phase 2 — 오픈마켓형 수익화** | 코스 가격·주문·쿠폰·강사 커미션/매출/정산 | ✅ 4.1.0 |
| **Phase 3 — 참여 기능** | Q&A·리뷰·과제·수료증 | ✅ 4.1.0 |
| **Phase 4 — 공개 프론트엔드** | 코스 카탈로그·강의 플레이어·체크아웃·학생 대시보드 (호스트로 publish되는 스캐폴딩) | ✅ 4.1.0 |

## 오픈마켓(마켓플레이스)

독립 강사가 코스를 판매하고 플랫폼이 커미션을 가져가는 모델입니다. `CheckoutService`가
주문 → 강사/플랫폼 수익 분배 → 결제 시 수강 등록을 처리합니다. 커미션 기본값은
`lms.marketplace.commission_rate`(기본 80%, 코스별 `commission_rate`로 재정의).
관리자 `Marketplace` 섹션에서 주문·쿠폰·매출·정산을, `Engagement` 섹션에서
리뷰·Q&A·과제·수료증을 관리합니다.

## 공개 프론트엔드 스캐폴딩

```bash
php artisan lms:install-frontend        # 덮어쓰기: --force
npm run build                            # 또는 npm run dev
```

코스 카탈로그(`/courses`), 코스 상세, 강의 플레이어(`/learn/...`), 체크아웃, 학생
대시보드(`/my-courses`)의 React/Inertia 페이지와 공개 컨트롤러·라우트를 호스트로
게시합니다. **라라벨 스타터킷처럼** 게시 후에는 호스트가 소유·자유롭게 수정합니다.
체크아웃 스캐폴딩은 데모용으로 즉시 결제 완료 처리하므로, 프로덕션에서는
`CheckoutController::store()`의 표시된 지점에 실제 결제 게이트웨이를 연결하세요.

## 요구사항

- PHP `^8.3`
- Laravel `^11.0 || ^12.0 || ^13.0`
- `cms-orbit/core` `^4.0`

## 설치

```bash
composer require cms-orbit/lms
php artisan migrate
```

백엔드(엔티티·마이그레이션·메뉴·권한)는 패키지 auto-discovery로 **설치만으로**
자동 등록됩니다. 호스트 파일 수정이 필요 없습니다.

## 도메인 모델

- `Course` (`lms_courses`) — 제목, 슬러그, 강사(user), 난이도, 상태, 카테고리, 소요시간
- `CourseSection` (`lms_course_sections`) — 코스 내 정렬된 섹션(TutorLMS의 "topic")
- `Lesson` (`lms_lessons`) — 섹션 내 동영상/텍스트 단위, `is_preview` 무료 미리보기
- `Quiz` / `QuizQuestion` — 단일/복수/참거짓 문항(`options`·`correct` JSON)
- `Enrollment` (`lms_enrollments`) — 수강생의 코스 등록, 진도율·상태
- `LessonProgress` (`lms_lesson_progress`) — 레슨별 완료. `Enrollment::recalculateProgress()`가 이를 집계해 진도율/완료를 갱신

## 설정

`config('lms.user_model')`로 강사/수강생 user 모델을 지정합니다(기본: 앱의 인증
user 모델). 설정을 게시하려면:

```bash
php artisan vendor:publish --tag=lms-config
```

## 드립 콘텐츠 & 플레이어 옵션

코스에서 드립을 켜고(`drip_enabled`) 방식(`drip_type`)을 고릅니다: 수강 후 N일,
특정 날짜, 순차(이전 레슨 완료), 선수 레슨. 레슨별로 `drip_days`/`drip_date`/
`drip_prerequisite_id`를 설정하며 `Lesson::isUnlockedFor($enrollment)`가 잠금을
판정합니다(무료 미리보기는 항상 열림). 플레이어는 코스별 옵션(탐색 불가,
빨리감기 불가, 자동재생, 완료 후 진행)을 지원하며 스캐폴딩 플레이어가 네이티브
비디오에서 이를 적용합니다.

## 이수증 & GUI 빌더 (관리자)

이수증 빌더는 **Orbit 관리자 엔티티**입니다. Engagement → Certificate Templates에서
템플릿을 만들고, 편집 화면의 커스텀 `ReactField`(`lms-certificate-builder`) 캔버스에서
텍스트/플레이스홀더를 드래그 배치합니다. 이 컴포넌트는 패키지가 소유하며
`frontend.json`의 `registrations`로 자동 등록됩니다(설치 후 `php artisan
orbit:frontend-sync && npm run build`; `cms-orbit/core ^4.0.12` 필요).

`{{student_name}}`·`{{course_title}}`·`{{instructor_name}}`·`{{issued_date}}`·
`{{serial}}` 치환을 지원합니다. `CertificateService::issue()`로 완료 수강에
발급하고 `renderHtml()`로 인쇄/PDF용 HTML을 렌더합니다. 학생은 스토어프론트의
`/certificates/{id}`에서 자신의 이수증을 보고 인쇄합니다.

## 강사 대시보드

`/instructor`(게시된 스캐폴딩)에서 강사별 코스·수강생·매출(총/정산가능/정산완료)·
최근 리뷰·Q&A를 확인합니다(`InstructorDashboardService`).

## 업데이트 노트

### 4.4.0

- **php 제약 `^8.3` → `^8.4`**: php 8.3 환경에서는 더 이상 설치되지 않습니다.
- **`cms-orbit/core` `^4.1` → `^4.4`**.
- **생산 의존은 하나도 바뀌지 않았습니다.** php 하한 상향으로 새로 받게 된 패키지는 Pest 5 계열(`require-dev`)뿐입니다. cms-orbit 전 패키지의 직접 의존 20개를 최신판과 전수 대조했고, 나머지 18개는 이미 php `^8.3` 에서 최신을 받고 있었습니다. 이번 상향은 기능 확보가 아니라 장기 정리 목적입니다.
- **소비자의 Laravel 11·12 지원은 유지됩니다** (`laravel/framework ^11.0 || ^12.0 || ^13.0`).

### 4.3.1

- **php 제약 `^8.3` 복구**: 게시된 태그는 모두 `^8.3` 이었으나 main 에서 `^8.2` 로 내려가 있었습니다. Laravel 13 은 php `^8.3` 을 요구하므로 `php ^8.2` + `laravel/framework ^13` 조합은 php 8.2 환경에서 조용히 Laravel 11 을 설치합니다.
- **`laravel/pint` `^1.14` → `^1.30`** (1.30 이 php `^8.3` 을 요구).
- **릴리스 파이프라인 도입**: `.githooks/pre-push` 가 composer.json 의 `version` 필드와 태그명이 어긋난 태그의 푸시를 차단합니다. `cms-orbit/core` 의 `4.0.8` 태그가 `version: 4.0.7` 로 만들어져 Packagist 가 아무 오류 없이 그 태그를 무시했고, 4.0.8 이 게시되지 않은 사실을 아무도 알지 못한 사고가 있었습니다. `bin/release <버전>` 이 version 갱신·검증·커밋·태그·푸시를 한 동작으로 묶어 이 드리프트를 원천 차단하고, `cms-orbit/*` 의존이 실제로 Packagist 에 게시되어 있는지 Composer 리졸버로 확인합니다. 저장소를 클론해 `composer install` 하면 `core.hooksPath` 가 자동 설정됩니다.

## License

MIT
