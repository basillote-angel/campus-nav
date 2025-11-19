<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Claim Approval Cancelled</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h2 style="color: #b91c1c;">Hi {{ $recipientName }},</h2>
    <p>Your approval for <strong>{{ $itemTitle }}</strong> was cancelled by the admin.</p>
    <p>The item is now available for other claimants. If you believe this was a mistake, please contact the admin office or submit a new claim with more detailed proof of ownership.</p>
    <p>Need help? Email <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> or call {{ $contactPhone }}.</p>
    <p style="margin-top: 32px;">— NavistFind Admin Office</p>
</body>
</html>





