<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Publishes the LMS public storefront as editable scaffolding into the host app
 * — the starter-kit model. Once published, the host owns and freely edits the
 * controllers, routes, and React pages; the package no longer manages them.
 */
class InstallFrontendCommand extends Command
{
    protected $signature = 'lms:install-frontend {--force : Overwrite files that already exist}';

    protected $description = 'Publish the LMS storefront scaffolding (controllers, routes, React pages) into the host application';

    /**
     * Stub (relative to stubs/frontend) => destination absolute path.
     *
     * @var array<string, string>
     */
    protected function fileMap(): array
    {
        $stubs = dirname(__DIR__, 2).'/stubs/frontend';

        return [
            $stubs.'/routes/lms.php.stub' => base_path('routes/lms.php'),
            $stubs.'/controllers/CatalogController.php.stub' => app_path('Http/Controllers/Lms/CatalogController.php'),
            $stubs.'/controllers/CheckoutController.php.stub' => app_path('Http/Controllers/Lms/CheckoutController.php'),
            $stubs.'/controllers/LearnController.php.stub' => app_path('Http/Controllers/Lms/LearnController.php'),
            $stubs.'/controllers/DashboardController.php.stub' => app_path('Http/Controllers/Lms/DashboardController.php'),
            $stubs.'/controllers/InstructorController.php.stub' => app_path('Http/Controllers/Lms/InstructorController.php'),
            $stubs.'/controllers/CertificateController.php.stub' => app_path('Http/Controllers/Lms/CertificateController.php'),
            $stubs.'/pages/courses/index.tsx.stub' => resource_path('js/pages/lms/courses/index.tsx'),
            $stubs.'/pages/courses/show.tsx.stub' => resource_path('js/pages/lms/courses/show.tsx'),
            $stubs.'/pages/checkout.tsx.stub' => resource_path('js/pages/lms/checkout.tsx'),
            $stubs.'/pages/learn.tsx.stub' => resource_path('js/pages/lms/learn.tsx'),
            $stubs.'/pages/dashboard.tsx.stub' => resource_path('js/pages/lms/dashboard.tsx'),
            $stubs.'/pages/instructor/dashboard.tsx.stub' => resource_path('js/pages/lms/instructor/dashboard.tsx'),
        ];
    }

    public function handle(Filesystem $files): int
    {
        $force = (bool) $this->option('force');

        foreach ($this->fileMap() as $stub => $destination) {
            if (! $files->exists($stub)) {
                $this->components->error("Missing stub: {$stub}");

                continue;
            }

            if ($files->exists($destination) && ! $force) {
                $this->components->warn('Skipped (exists): '.$this->relative($destination));

                continue;
            }

            $files->ensureDirectoryExists(dirname($destination));
            $files->copy($stub, $destination);
            $this->components->info('Published: '.$this->relative($destination));
        }

        $this->registerRoutes($files);

        $this->newLine();
        $this->components->info('LMS storefront published. Run `npm run build` (or `npm run dev`) to compile the pages.');

        return self::SUCCESS;
    }

    /**
     * Wire routes/lms.php into the host web routes exactly once.
     */
    protected function registerRoutes(Filesystem $files): void
    {
        $webRoutes = base_path('routes/web.php');
        $marker = "require __DIR__.'/lms.php';";

        if (! $files->exists($webRoutes)) {
            $this->components->warn('routes/web.php not found; add "'.$marker.'" manually.');

            return;
        }

        $contents = $files->get($webRoutes);

        if (str_contains($contents, $marker)) {
            return;
        }

        $files->append($webRoutes, PHP_EOL.'// CMS Orbit LMS storefront routes'.PHP_EOL.$marker.PHP_EOL);
        $this->components->info('Registered routes/lms.php in routes/web.php');
    }

    protected function relative(string $path): string
    {
        return str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
    }
}
