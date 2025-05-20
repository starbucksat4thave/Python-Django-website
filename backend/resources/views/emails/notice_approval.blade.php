<!DOCTYPE html>
<html lang="en">

<head>
    <title>New Notice for Approval</title>
</head>

<body>
    <p>A new notice requires your approval:</p>
    <p><strong>Title:</strong> {{ $title }}</p>
    <p><strong>Content:</strong> {{ $content }}</p>
    <p><strong>File:</strong></p>
    <img src="{{ $imageUrl }}" alt="Notice File" style="max-width: 100%; height: auto;">
    <p>
        <a href="{{ $approveUrl }}"
            style="background-color: #4CAF50; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block;">
            Approve Notice
        </a>
    </p>
    <p>Thank you for using our application!</p>
</body>

</html>
