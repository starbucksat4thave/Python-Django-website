<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Verification</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .container { border: 2px solid green; padding: 20px; display: inline-block; background: #f4f4f4; }
        .valid { color: green; font-size: 24px; font-weight: bold; }
        .photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #333;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="valid">✅ ID Verified</h2>

    @if($user->image)
        <img src="{{ asset('storage/' . $user->image) }}" alt="User" class="photo">
    @else
        <p><em>No photo available</em></p>
    @endif

    <p><strong>Name:</strong> {{ strtoupper($user->name) }}</p>
    <p><strong>Department:</strong> {{ strtoupper($user->department?->name ?? 'N/A') }}</p>
    <p><strong>Roll No:</strong> {{ $user->university_id }}</p>
    <p><strong>Session:</strong> {{ $user->session }} - {{ $user->session + 1 }}</p>
</div>
</body>
</html>
