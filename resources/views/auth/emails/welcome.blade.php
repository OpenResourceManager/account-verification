A User Verification account has been created for you. Before you can log in you must create a password.

<a href="{{ $link = url('password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}"> Click here
    to create a password. </a>