<?php

namespace App\Services\Auth;

final class AuthFailureStage
{
    public const VALIDATION = 'validation';

    public const RATE_LIMIT = 'rate_limit';

    public const EMAIL_NOT_FOUND = 'email_not_found';

    public const PASSWORD_INVALID = 'password_invalid';

    public const ACCOUNT_BLOCKED = 'account_blocked';

    public const SESSION_LOGIN_FAILED = 'session_login_failed';

    public const TWO_FACTOR_NO_PENDING = 'two_factor_no_pending';

    public const TWO_FACTOR_SUBJECT_MISSING = 'two_factor_subject_missing';

    public const TWO_FACTOR_NO_SESSION = 'two_factor_no_session';

    public const TWO_FACTOR_EXPIRED = 'two_factor_expired';

    public const TWO_FACTOR_CODE_INVALID = 'two_factor_code_invalid';

    public const TWO_FACTOR_RATE_LIMIT = 'two_factor_rate_limit';

    public const LOCKOUT = 'lockout';
}
