<!DOCTYPE html>
<html lang="en">
<head>
    <title>Application Template</title>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12pt; }
        /*.header, .footer { text-align: center; }*/
        .signature { margin-top: 60px; }
    </style>
</head>
<body>
<div class="header">
    <p>Date: {{ now()->format('F d, Y') }}</p>
    <p>To,</p>
    <p>{{ $application->authorizedBy->name }}</p>
    <p>{{ ucfirst($application->authorizedBy->designation) }}</p>
    <p>Department of {{ $application->authorizedBy->department->name }}</p>
    <p>Artisan Forces University of Technology</p>
</div>

<br><br>

<p><strong>Subject:</strong> {{ $application->applicationTemplate->title }}</p>

<br>

<p>{!! nl2br(e($application->body)) !!}</p>

<br><br>

<div class="signature">
    <p>Yours sincerely,</p>
    <p>{{ $application->user->name }}</p>
    <p>ID: {{ $application->user->university_id }}</p>
    <p>Session: {{ $application->user->session }}</p>
    <p>Department of {{ $application->user->department->name }}</p>
</div>
</body>
</html>
