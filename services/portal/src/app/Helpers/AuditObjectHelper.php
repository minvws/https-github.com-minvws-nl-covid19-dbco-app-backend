<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Eloquent\EloquentCase;
use MinVWS\Audit\Models\AuditObject;

class AuditObjectHelper
{
    private const COUNT_ACTION_EDIT = 'edit';
    private const COUNT_ACTION_SHOW = 'show';
    private const COUNT_ACTION_SHOW_DENIED = 'show-denied';
    private const COUNT_ACTION_EXPORTED = 'exported';
    private const COUNT_ACTION_DOSSIER_DELETED = 'dossier-deleted';
    private const COUNT_ACTION_DOSSIER_DELETE_STARTED = 'dossier-delete-started';
    private const COUNT_ACTION_DOSSIER_DELETE_RECOVERED = 'dossier-delete-recovered';
    private const COUNT_ACTION_CONTACT_DELETED = 'contact-deleted';
    private const COUNT_ACTION_CONTACT_DELETE_STARTED = 'contact-delete-started';
    private const COUNT_ACTION_CONTACT_DELETE_RECOVERED = 'contact-delete-recovered';

    /**
     * Set Organisation data in case AuditObject
     */
    public static function setAuditObjectOrganisation(AuditObject $auditObject, EloquentCase $case): void
    {
        $auditObject->detail('organisationId', $case->organisation->external_id);
        $auditObject->detail('organisationName', $case->organisation->name);
    }

    public static function setAuditObjectCountEdit(AuditObject $auditObject): void
    {
        self::setAuditObjectCountAction($auditObject, self::COUNT_ACTION_EDIT);
    }

    public static function setAuditObjectCountShow(AuditObject $auditObject): void
    {
        self::setAuditObjectCountAction($auditObject, self::COUNT_ACTION_SHOW);
    }

    public static function setAuditObjectCountShowDenied(AuditObject $auditObject): void
    {
        self::setAuditObjectCountAction($auditObject, self::COUNT_ACTION_SHOW_DENIED);
    }

    public static function setAuditObjectCountExported(AuditObject $auditObject): void
    {
        self::setAuditObjectCountAction($auditObject, self::COUNT_ACTION_EXPORTED);
    }

    public static function setAuditObjectCountDossierDeleted(AuditObject $auditObject): void
    {
        self::setAuditObjectCountAction($auditObject, self::COUNT_ACTION_DOSSIER_DELETED);
    }

    public static function setAuditObjectCountDossierDeleteStarted(AuditObject $auditObject): void
    {
        self::setAuditObjectCountAction($auditObject, self::COUNT_ACTION_DOSSIER_DELETE_STARTED);
    }

    public static function setAuditObjectCountDossierDeleteRecovered(AuditObject $auditObject): void
    {
        self::setAuditObjectCountAction($auditObject, self::COUNT_ACTION_DOSSIER_DELETE_RECOVERED);
    }

    public static function setAuditObjectCountContactDeleted(AuditObject $auditObject): void
    {
        self::setAuditObjectCountAction($auditObject, self::COUNT_ACTION_CONTACT_DELETED);
    }

    public static function setAuditObjectCountContactDeleteStarted(AuditObject $auditObject): void
    {
        self::setAuditObjectCountAction($auditObject, self::COUNT_ACTION_CONTACT_DELETE_STARTED);
    }

    public static function setAuditObjectCountContactDeleteRecovered(AuditObject $auditObject): void
    {
        self::setAuditObjectCountAction($auditObject, self::COUNT_ACTION_CONTACT_DELETE_RECOVERED);
    }

    private static function setAuditObjectCountAction(AuditObject $auditObject, string $countAction): void
    {
        $auditObject->detail('count', $countAction);
    }
}
