<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $lesson->title }} - {{ $lesson->course->title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            line-height: 1.6;
            font-size: 14px;
        }
        h1, h2, h3 {
            color: #0d6efd;
            margin-bottom: 10px;
        }
        .course-title {
            text-align: center;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .lesson-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .lesson-content p {
            margin-bottom: 10px;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 6px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="course-title">
        <h1>{{ $lesson->course->title }}</h1>
    </div>

    <div class="lesson-section">
        <div class="lesson-title">{{ $lesson->title }}</div>

        <div class="lesson-content">
            {!! $lesson->content ?? '<p>No content available for this lesson.</p>' !!}
        </div>
    </div>
</body>
</html>
