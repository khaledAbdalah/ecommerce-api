<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #111827;
            padding: 20px;
        }

        .container {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
        }

        .header {
            font-size: 20px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 15px;
        }

        .section {
            margin-top: 15px;
        }

        .section-title {
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .section-content {
            background-color: #f3f4f6;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            color: #374151;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">Server Error Detected</div>

    @foreach($data as $key => $value)
        <div class="section">
            <div class="section-title">{{ ucfirst($key) }}:</div>
            <div class="section-content">
                {{ $value  }}
            </div>
        </div>
    @endforeach
</div>
</body>
</html>