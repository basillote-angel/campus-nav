<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Claim Update</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h2 style="color: #b91c1c;">Hi {{ $recipientName }},</h2>
    <p>We reviewed your claim for <strong>{{ $itemTitle }}</strong>, but unfortunately we could not approve it.</p>
    <p><strong>Reason:</strong> {{ $rejectionReason }}</p>
    <p>You can submit a new claim with more specific details (brand, color, unique identifiers, proof of ownership) or reach out to the admin office for clarification.</p>
    <p>Need help? Contact us at <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> or call {{ $contactPhone }}.</p>
    <p style="margin-top: 32px;">— NavistFind Admin Office</p>
</body>
</html>





