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
| Phase 2 — 수익화 | 코스 가격·주문·쿠폰·매출 | 예정 |
| Phase 3 — 참여 기능 | Q&A·리뷰·과제·수료증 | 예정 |
| Phase 4 — 공개 프론트엔드 | 코스 카탈로그·강의 플레이어·학생 대시보드 (호스트로 publish되는 스캐폴딩) | 예정 |

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

## License

MIT
