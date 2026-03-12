<?php

declare(strict_types=1);

namespace App\Constants;

class ErrorMessages
{
    // Autorizācijas un atļauju kļūdu ziņojumi
    public const ACCESS_FORBIDDEN             = 'Jums nav piekļuves tiesību šai lapai.';
    public const SERVICE_EDIT_FORBIDDEN       = 'Jums nav tiesību rediģēt šo pakalpojumu.';
    public const SERVICE_DELETE_FORBIDDEN     = 'Jums nav tiesību dzēst šo pakalpojumu.';
    public const JOB_REQUEST_EDIT_FORBIDDEN   = 'Jums nav tiesību rediģēt šo darba sludinājumu.';
    public const JOB_REQUEST_DELETE_FORBIDDEN = 'Jums nav tiesību dzēst šo darba sludinājumu.';
    public const OWN_SERVICE_APPLICATION      = 'Nevar pieteikties uz savu pakalpojumu.';

    // Validācijas kļūdu ziņojumi
    public const CATEGORY_SELF_PARENT         = 'Kategorija nevar būt pakārtota sev.';
    public const CATEGORY_HAS_CHILDREN        = 'Nevar izdzēst kategoriju, kurai ir apakškategorijas.';
    public const CATEGORY_HAS_JOB_REQUESTS    = 'Nevar izdzēst kategoriju, jo tai ir piesaistīti darba sludinājumi.';

    // Error flash ziņojumi
    public const PROFILE_NOT_FOUND            = 'Lūdzu, vispirms izveidojiet profilu!';
    public const PROFILE_INCOMPLETE           = 'Lai turpinātu, lūdzu, norādiet savu pilsētu un telefona numuru!';
}
