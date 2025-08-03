<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Email Verification Code</title>
</head>

<body>
    <h2>Hello {{ $user->name ?? 'User' }},</h2>

    <p>Your verification code is:</p>

    <h1 style="color: #2d3748;">{{ $user->verify_otp }}</h1>

    <p>This code will expire in 10 minutes.</p>

    <p>If you didn’t request this, you can ignore this message.</p>

    <br>
    <p>Thanks,<br>{{ config('app.name') }} Team</p>
</body>

</html>
