<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Models\CertificateTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificateTemplate>
 */
class CertificateTemplateFactory extends Factory
{
    protected $model = CertificateTemplate::class;

    public function definition(): array
    {
        return [
            'name' => 'Default certificate',
            'orientation' => 'landscape',
            'width' => 1123,
            'height' => 794,
            'background' => null,
            'elements' => [
                ['id' => 'title', 'text' => 'Certificate of Completion', 'x' => 561, 'y' => 200, 'font_size' => 48, 'color' => '#111827', 'align' => 'center', 'bold' => true],
                ['id' => 'name', 'text' => '{{student_name}}', 'x' => 561, 'y' => 360, 'font_size' => 36, 'color' => '#4338ca', 'align' => 'center', 'bold' => true],
                ['id' => 'course', 'text' => 'has successfully completed {{course_title}}', 'x' => 561, 'y' => 440, 'font_size' => 22, 'color' => '#374151', 'align' => 'center', 'bold' => false],
                ['id' => 'meta', 'text' => 'Issued {{issued_date}} · {{serial}}', 'x' => 561, 'y' => 620, 'font_size' => 16, 'color' => '#6b7280', 'align' => 'center', 'bold' => false],
            ],
            'is_default' => true,
        ];
    }
}
