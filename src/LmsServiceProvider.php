<?php

declare(strict_types=1);

namespace CmsOrbit\Lms;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Core\Foundation\Entity\EntityRegistry;
use CmsOrbit\Core\Foundation\ItemPermission;
use CmsOrbit\Core\Foundation\OrbitServiceProvider;
use CmsOrbit\Core\Support\Facades\Orbit;
use CmsOrbit\Core\Support\Locale;
use CmsOrbit\Lms\Console\InstallFrontendCommand;
use CmsOrbit\Lms\Entities\AssignmentEntity;
use CmsOrbit\Lms\Entities\CertificateEntity;
use CmsOrbit\Lms\Entities\CertificateTemplateEntity;
use CmsOrbit\Lms\Entities\CouponEntity;
use CmsOrbit\Lms\Entities\CourseEntity;
use CmsOrbit\Lms\Entities\CourseQuestionEntity;
use CmsOrbit\Lms\Entities\CourseSectionEntity;
use CmsOrbit\Lms\Entities\EarningEntity;
use CmsOrbit\Lms\Entities\EnrollmentEntity;
use CmsOrbit\Lms\Entities\LessonEntity;
use CmsOrbit\Lms\Entities\OrderEntity;
use CmsOrbit\Lms\Entities\PayoutEntity;
use CmsOrbit\Lms\Entities\QuizEntity;
use CmsOrbit\Lms\Entities\QuizQuestionEntity;
use CmsOrbit\Lms\Entities\ReviewEntity;

/**
 * Registers the LMS domain with Orbit: entity descriptors (menu / permissions /
 * CRUD routes), migrations, and translations. Everything self-registers on
 * `composer require` via package auto-discovery — no host file edits required.
 */
class LmsServiceProvider extends OrbitServiceProvider
{
    /**
     * @var array<int, class-string<Entity>>
     */
    protected array $entities = [
        CourseEntity::class,
        CourseSectionEntity::class,
        LessonEntity::class,
        QuizEntity::class,
        QuizQuestionEntity::class,
        EnrollmentEntity::class,
        // Marketplace (Phase 2)
        OrderEntity::class,
        CouponEntity::class,
        EarningEntity::class,
        PayoutEntity::class,
        // Engagement (Phase 3)
        ReviewEntity::class,
        CourseQuestionEntity::class,
        AssignmentEntity::class,
        CertificateEntity::class,
        CertificateTemplateEntity::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/lms.php', 'lms');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallFrontendCommand::class,
            ]);
        }

        // Register entities as soon as Core's EntityRegistry resolves. Core loads
        // entity CRUD routes during its own boot(), which may run before this
        // package's boot(), so registering in boot() would be too late.
        $this->app->afterResolving(EntityRegistry::class, function (EntityRegistry $registry): void {
            $registry->registerClass($this->entities);
        });

        if ($this->app->resolved(EntityRegistry::class)) {
            $this->app->make(EntityRegistry::class)->registerClass($this->entities);
        }

        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');
        Locale::registerPath(__DIR__.'/../resources/lang');
    }

    public function boot(): void
    {
        Orbit::registerSection('lms', 'bs.mortarboard', __('Learning'), 5300);
        Orbit::registerSection('lms-marketplace', 'bs.shop', __('Marketplace'), 5400);
        Orbit::registerSection('lms-engagement', 'bs.chat-square-heart', __('Engagement'), 5500);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/lms.php' => config_path('lms.php'),
        ], 'lms-config');

        $this->registerPermissions();
    }

    protected function registerPermissions(): void
    {
        $group = ItemPermission::group(__('Learning'));

        foreach ($this->entities as $entity) {
            $uriKey = $entity::uriKey();
            $label = (new $entity)->label();

            $group
                ->addPermission("lms.entities.{$uriKey}.viewAny", __('View :label', ['label' => $label]))
                ->addPermission("lms.entities.{$uriKey}.view", __('View :label item', ['label' => $label]))
                ->addPermission("lms.entities.{$uriKey}.create", __('Create :label', ['label' => $label]))
                ->addPermission("lms.entities.{$uriKey}.update", __('Update :label', ['label' => $label]))
                ->addPermission("lms.entities.{$uriKey}.delete", __('Delete :label', ['label' => $label]));
        }

        Orbit::registerPermission($group);
    }
}
