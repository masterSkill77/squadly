<?php

namespace App\Enums;

enum DocumentType: string
{
    case MedicalCertificate = 'medical_certificate';
    case SportLicense = 'sport_license';
    case IdentityPhoto = 'identity_photo';
    case ParentalAuthorization = 'parental_authorization';
    case InsuranceCertificate = 'insurance_certificate';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MedicalCertificate => 'Certificat médical',
            self::SportLicense => 'Licence sportive',
            self::IdentityPhoto => 'Photo d\'identité',
            self::ParentalAuthorization => 'Autorisation parentale',
            self::InsuranceCertificate => 'Attestation d\'assurance',
            self::Other => 'Autre',
        };
    }

    public static function requiredTypes(): array
    {
        return [
            self::MedicalCertificate,
            self::SportLicense,
            self::IdentityPhoto,
            self::InsuranceCertificate,
        ];
    }
}
