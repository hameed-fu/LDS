<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            padding: 50px;
            background: #f0f4f8;
        }

        .certificate {
            max-width: 800px;
            margin: auto;
            border: 10px solid #3b82f6;
            border-radius: 20px;
            padding: 60px 50px;
            background: linear-gradient(145deg, #ffffff, #e0e7ff);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }

        h1 {
            font-family: 'Georgia', serif;
            font-size: 48px;
            color: #3b82f6;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 34px;
            margin: 20px 0;
            color: #111827;
        }

        h2.highlight {
            color: #2563eb;
            font-weight: bold;
        }

        p {
            font-size: 20px;
            margin: 10px 0;
            color: #374151;
        }

        .line {
            width: 180px;
            height: 3px;
            background: #3b82f6;
            margin: 25px auto;
            border-radius: 2px;
        }

        .footer {
            margin-top: 50px;
            font-size: 16px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <h1>Certificate of Completion</h1>
        <div class="line"></div>
        <p>This is to certify that</p>
        <h2 class="highlight">{{ $user->name }}</h2>
        <p>has successfully completed the course</p>
        <h2 class="highlight">{{ $course->title }}</h2>
        <p class="footer">Issued on {{ $issued_at->format('F j, Y') }}</p>
    </div>
</body>
</html>
