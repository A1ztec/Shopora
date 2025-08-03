<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Password Reset Code</title>
</head>

<body>
    <p>Hello {{ $user->name ?? 'User' }},</p>

    <p>You requested a password reset. Use the code below to reset your password:</p>

    <h2>{{ $user->password_reset_code }}</h2>

    <p>This code will expire in 10 minutes.</p>

    <p>If you didn't request this, please ignore the email.</p>

    <p>Thanks,<br>The Team</p>
</body>

</html>
