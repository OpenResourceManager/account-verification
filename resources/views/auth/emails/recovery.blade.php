Your User Verification account has been reactivated. Before you can log in you must set your password.

<a href="{{ $link = url('password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}"> Click here to set your password. </a>