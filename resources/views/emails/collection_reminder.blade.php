<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collection Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h2 style="color: #0f172a;">Hi {{ $recipientName }},</h2>
    <p>{{ $stageMessage }}</p>
    <p><strong>Item:</strong> {{ $itemTitle }}</p>
    <ul>
        <li><strong>Collection deadline:</strong> {{ $deadlineText }}</li>
        <li><strong>Location:</strong> {{ $officeLocation }}</li>
        <li><strong>Office hours:</strong> {{ $officeHours }}</li>
        <li><strong>Bring:</strong> Valid ID (student or government)</li>
    </ul>
    <p>If you have any questions or need more time, contact us at <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> or call {{ $contactPhone }}.</p>
    <p style="margin-top: 32px;">Thanks,<br>NavistFind Admin Office</p>
</body>
</html>





