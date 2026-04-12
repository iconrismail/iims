<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline — IIMS HR</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0f1923;
            color: #e8edf2;
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            padding: 3rem 2rem;
            max-width: 420px;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            display: block;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #e8edf2;
        }
        p {
            color: #8899aa;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            background: #00e5ff;
            color: #0f1923;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #00b8d4;
        }
        .brand {
            margin-top: 3rem;
            font-size: 0.85rem;
            color: #556677;
        }
        .brand span {
            color: #00e5ff;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="icon">📵</span>
        <h1>You're offline</h1>
        <p>It looks like you've lost your internet connection. Check your network settings and try again.</p>
        <button class="btn" onclick="window.location.reload()">Retry Connection</button>
        <div class="brand">
            <span>IIMS</span> Payroll & HR System
        </div>
    </div>
</body>
</html>
