@if($company_logo_url)
<img src="{{$company_logo_url}}" />
<br />
@endif
<p>
    Hello {{$name}},
</p>
<p>
    This is a password change notification email from {{$company_name}}.
    Your account password has been reset by our support team.
</p>
<p>
    <b>
        Be advised that it may take over 15 minutes for this password reset to become effective.
    </b>
</p>
<p>
    Your new password is: <b>{{$password}}</b>
</p>
@if($self_service_url)
    <p>
        We strongly suggest that you, change you password again through our self service page. Please visit <a
                href="{{$self_service_url}}">{{$self_service_url}}</a> to change your password to something you will
        remember.
    </p>
@endif
