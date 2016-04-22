<p>
You have requested to change your password. If you did not, ignore this email.
</p>
<a href="{{ $link = url('password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}"> Click here
    to change your password. </a>
