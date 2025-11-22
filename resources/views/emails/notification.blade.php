<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            border-bottom: 2px solid #6366f1;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .app-name {
            font-size: 24px;
            font-weight: bold;
            color: #6366f1;
            margin: 0;
        }
        .title {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin: 20px 0;
        }
        .body {
            color: #4b5563;
            white-space: pre-wrap;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #e0e7ff;
            color: #4338ca;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 10px;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 class="app-name">{{ $appName }}</h1>
        </div>
        
        <h2 class="title">{{ $title }}</h2>
        
        <div class="body">{{ $body }}</div>
        
        @if($score)
        <div>
            <span class="badge">Match Score: {{ $score }}%</span>
        </div>
        @endif
        
        <div class="footer">
            <p>This is an automated notification from {{ $appName }}.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>










