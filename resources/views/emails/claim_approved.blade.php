<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Claim Approved</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h2 style="color: #0f172a;">Hi {{ $recipientName }},</h2>
    <p>Your claim for <strong>{{ $itemTitle }}</strong> has been <strong>approved</strong>! 🎉</p>
    <p>Please visit the admin office to collect your item:</p>
    <ul>
        <li><strong>Location:</strong> {{ $officeLocation }}</li>
        <li><strong>Office Hours:</strong> {{ $officeHours }}</li>
        <li><strong>Collection Deadline:</strong> {{ $collectionDeadlineText }}</li>
        <li><strong>Bring:</strong> Valid ID (Student ID or Government ID)</li>
    </ul>
    <p>If you have any questions, reach us at <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> or call {{ $contactPhone }}.</p>
    <p style="margin-top: 32px;">See you soon,<br>NavistFind Admin Office</p>
</body>
</html>





