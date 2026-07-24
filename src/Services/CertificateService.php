<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Services;

use CmsOrbit\Lms\Enums\EnrollmentStatus;
use CmsOrbit\Lms\Models\Certificate;
use CmsOrbit\Lms\Models\CertificateTemplate;
use CmsOrbit\Lms\Models\Enrollment;

/**
 * Issues completion certificates and renders them from a designed template
 * (positioned text elements with {{placeholders}}).
 */
class CertificateService
{
    /**
     * Issue a certificate for a completed enrollment. Idempotent per enrollment.
     */
    public function issue(Enrollment $enrollment, ?CertificateTemplate $template = null): Certificate
    {
        $template ??= CertificateTemplate::default();

        return Certificate::query()->firstOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'course_id' => $enrollment->course_id,
                'student_id' => $enrollment->student_id,
                'certificate_template_id' => $template?->getKey(),
                'template' => $template?->name ?? 'default',
            ],
        );
    }

    public function issueIfCompleted(Enrollment $enrollment, ?CertificateTemplate $template = null): ?Certificate
    {
        if ($enrollment->status !== EnrollmentStatus::Completed) {
            return null;
        }

        return $this->issue($enrollment, $template);
    }

    /**
     * Placeholder values available to certificate templates.
     *
     * @return array<string, string>
     */
    public function placeholders(Certificate $certificate): array
    {
        $certificate->loadMissing(['student', 'course.instructor']);

        return [
            'student_name' => (string) ($certificate->student?->name ?? ''),
            'course_title' => (string) ($certificate->course?->title ?? ''),
            'instructor_name' => (string) ($certificate->course?->instructor?->name ?? ''),
            'issued_date' => optional($certificate->issued_at)->format('Y-m-d') ?? '',
            'serial' => (string) $certificate->serial,
        ];
    }

    /**
     * Render the certificate as a standalone HTML document using its template's
     * positioned elements. Suitable for printing or PDF conversion.
     */
    public function renderHtml(Certificate $certificate): string
    {
        $template = $certificate->certificateTemplate ?? CertificateTemplate::default();
        $placeholders = $this->placeholders($certificate);

        $width = $template?->width ?? 1123;
        $height = $template?->height ?? 794;
        $background = $template?->background;
        $elements = is_array($template?->elements) ? $template->elements : [];

        $nodes = '';
        foreach ($elements as $element) {
            $text = $this->substitute((string) ($element['text'] ?? ''), $placeholders);
            $x = (int) ($element['x'] ?? 0);
            $y = (int) ($element['y'] ?? 0);
            $size = (int) ($element['font_size'] ?? 20);
            $color = $this->safeColor((string) ($element['color'] ?? '#111827'));
            $align = in_array($element['align'] ?? 'center', ['left', 'center', 'right'], true) ? $element['align'] : 'center';
            $weight = ! empty($element['bold']) ? 700 : 400;
            $translate = $align === 'center' ? 'translate(-50%, -50%)' : ($align === 'right' ? 'translate(-100%, -50%)' : 'translate(0, -50%)');

            $nodes .= sprintf(
                '<div style="position:absolute;left:%dpx;top:%dpx;transform:%s;font-size:%dpx;color:%s;font-weight:%d;text-align:%s;white-space:nowrap;">%s</div>',
                $x,
                $y,
                $translate,
                $size,
                $color,
                $weight,
                $align,
                e($text),
            );
        }

        $bg = $background ? sprintf('background-image:url(%s);background-size:cover;background-position:center;', e($background)) : 'background:#ffffff;';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Certificate {$placeholders['serial']}</title>
<style>
  * { margin: 0; box-sizing: border-box; }
  body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f3f4f6; font-family: Georgia, 'Times New Roman', serif; }
  .certificate { position: relative; width: {$width}px; height: {$height}px; {$bg} box-shadow: 0 10px 40px rgba(0,0,0,.15); }
  @media print { body { background: #fff; } .certificate { box-shadow: none; } }
</style>
</head>
<body>
  <div class="certificate">{$nodes}</div>
</body>
</html>
HTML;
    }

    /**
     * @param  array<string, string>  $placeholders
     */
    protected function substitute(string $text, array $placeholders): string
    {
        return preg_replace_callback('/\{\{\s*([a-z_]+)\s*\}\}/', function (array $match) use ($placeholders): string {
            return $placeholders[$match[1]] ?? $match[0];
        }, $text) ?? $text;
    }

    protected function safeColor(string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) === 1 ? $color : '#111827';
    }
}
