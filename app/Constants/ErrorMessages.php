<?php

declare(strict_types=1);

namespace App\Constants;

class ErrorMessages
{
    public const ACCESS_FORBIDDEN = 'Jums nav piekļuves tiesību šai lapai.';
    public const SERVICE_EDIT_FORBIDDEN = 'Jums nav tiesību rediģēt šo pakalpojumu.';
    public const SERVICE_DELETE_FORBIDDEN = 'Jums nav tiesību dzēst šo pakalpojumu.';
    public const JOB_REQUEST_EDIT_FORBIDDEN = 'Jums nav tiesību rediģēt šo darba sludinājumu.';
    public const JOB_REQUEST_DELETE_FORBIDDEN = 'Jums nav tiesību dzēst šo darba sludinājumu.';
    public const OWN_SERVICE_APPLICATION         = 'Nevar pieteikties uz savu pakalpojumu.';
    public const OWN_JOB_REQUEST_APPLICATION     = 'Nevar pieteikties uz savu darba sludinājumu.';
    public const JOB_APPLICATION_ALREADY_APPLIED     = 'Jūs jau esat pieteicies šim darba sludinājumam.';
    public const SERVICE_APPLICATION_ALREADY_APPLIED = 'Jūs jau esat pieteicies šim pakalpojumam.';
    public const APPLICATION_CANCEL_FORBIDDEN  = 'Jums nav tiesību atcelt šo pieteikumu.';
    public const APPLICATION_NOT_CANCELLABLE   = 'Šo pieteikumu nevar atcelt, jo tas jau ir pieņemts vai pabeigts.';
    public const APPLICATION_NOT_PENDING       = 'Šis pieteikums vairs nav aktīvs.';
    public const JOB_NOT_YOURS                 = 'Šis sludinājums jums nepieder.';
    public const SERVICE_APPLICATION_NOT_YOURS = 'Šis pieteikums nepieder jūsu pakalpojumam.';
    public const JOB_NOT_ACTIVE                = 'Šis sludinājums vairs nav aktīvs.';
    public const JOB_NOT_ASSIGNED              = 'Darbs nav piešķirts — nevar atzīmēt kā pabeigtu.';
    public const JOB_NOT_COMPLETED             = 'Atsauksmes var atstāt tikai pēc darba pabeigšanas.';
    public const REVIEW_ALREADY_SUBMITTED      = 'Jūs jau esat atstājuši atsauksmi par šo darbu.';
    public const REVIEW_NOT_PARTICIPANT        = 'Jūs neesat šī darba dalībnieks.';

    public const ROLE_NOT_AVAILABLE  = 'Jums nav šīs lomas.';
    public const ROLE_ALREADY_EXISTS = 'Jums jau ir šī loma.';

    public const CATEGORY_SELF_PARENT = 'Kategorija nevar būt pakārtota sev.';
    public const CATEGORY_HAS_CHILDREN = 'Nevar izdzēst kategoriju, kurai ir apakškategorijas.';
    public const CATEGORY_HAS_JOB_REQUESTS = 'Nevar izdzēst kategoriju, jo tai ir piesaistīti darba sludinājumi.';

    public const PROFILE_NOT_FOUND = 'Lūdzu, vispirms izveidojiet profilu!';
    public const PROFILE_INCOMPLETE = 'Lai turpinātu, lūdzu, norādiet savu pilsētu un telefona numuru!';
}
