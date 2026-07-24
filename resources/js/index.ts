/**
 * LMS admin frontend registrations. Imported by the host aggregator that
 * `orbit:frontend-sync` generates, so these custom components resolve inside the
 * Orbit admin screen renderer without any host file edits.
 */
import { registerComponents } from '@cms-orbit/core';
import CertificateBuilderField from './fields/certificate-builder';

registerComponents({
    'lms-certificate-builder': CertificateBuilderField,
});

export { default as CertificateBuilderField } from './fields/certificate-builder';
