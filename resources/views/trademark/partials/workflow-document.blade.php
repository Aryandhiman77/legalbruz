<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #1f2937; line-height: 1.6; }
        h1 { margin-bottom: 8px; }
        .meta { color: #6b7280; margin-bottom: 24px; }
        .panel { border: 1px solid #d1d5db; border-radius: 12px; padding: 20px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">
        Application #{{ $application->id }}
        @if ($application->brand_name)
            | {{ $application->brand_name }}
        @endif
    </div>
    <div class="panel">
        <p>{{ $body }}</p>
    </div>
</body>
</html>
