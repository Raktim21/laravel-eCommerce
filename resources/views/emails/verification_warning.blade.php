<!DOCTYPE html>
<html>
<head>
    <title>Pending Email Verification</title>
</head>
<body>
<p>
    Dear {{ $user->name }}, <br>
    Your email has not been verified yet. Please verify your email before {{ \Carbon\Carbon::now()->addMonth()->format('d-m-Y') }}.
</p>
<p>Regards,
    <br>
    Selopia Ecommerce Team.
</p>
</body>
</html>
