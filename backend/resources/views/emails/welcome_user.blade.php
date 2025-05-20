<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome to {{ config('app.name') }}</h2>
    <p>Hello, {{ $user->name }}</p>
    <p>We're excited to have you on board! To complete your registration, please set your password by clicking the button below:</p>
    <p>
        <a href="{{ $resetUrl }}" class="button">Set Your Password</a>
    </p>
    <p>If you are not a member of this institute, please ignore this email.</p>
    <div class="footer">
        <p>Thank you for joining {{ config('app.name') }}!</p>
    </div>
</div>
</body>
</html>

