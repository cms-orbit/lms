<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use CmsOrbit\Lms\Database\Factories\CertificateTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A designable certificate layout. `elements` holds positioned text nodes with
 * {{placeholders}} authored in the GUI builder.
 */
class CertificateTemplate extends Model
{
    /** @use HasFactory<CertificateTemplateFactory> */
    use HasFactory;

    protected $table = 'lms_certificate_templates';

    protected $fillable = [
        'name',
        'orientation',
        'width',
        'height',
        'background',
        'elements',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'elements' => 'array',
            'is_default' => 'boolean',
        ];
    }

    protected static function newFactory(): CertificateTemplateFactory
    {
        return CertificateTemplateFactory::new();
    }

    public static function default(): ?self
    {
        return static::query()->where('is_default', true)->first()
            ?? static::query()->latest('id')->first();
    }
}
