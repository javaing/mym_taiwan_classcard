<!doctype html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MYM TAIWAN - @yield('title')</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #fff9e5;
            color: #5f532f;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Microsoft JhengHei", sans-serif;
        }

        .preview-page {
            width: min(100%, 440px);
            margin: 0 auto;
            padding: 32px 22px 48px;
            text-align: center;
        }

        .greeting {
            margin: 0;
            color: #8c6f2d;
            font-size: 26px;
            font-weight: 600;
        }

        .date {
            margin: 8px 0 30px;
            color: #8c6f2d;
            font-size: 18px;
        }

        .activity-icon {
            width: 92px;
            height: 92px;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .activity-button {
            width: 100%;
            min-height: 122px;
            margin-bottom: 18px;
            padding: 14px 22px;
            border: 2px solid #9aae5c;
            border-radius: 18px;
            background: #fff;
            color: #6a5a2f;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 22px;
            cursor: pointer;
            box-shadow: 0 5px 14px rgba(111, 100, 54, 0.12);
        }

        .activity-button:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(111, 100, 54, 0.12);
        }

        .activity-button:disabled {
            border-color: #c9c9c9;
            background: #f2f2f2;
            color: #888;
            cursor: not-allowed;
            box-shadow: none;
            opacity: 0.78;
        }

        .activity-button:disabled .activity-price {
            color: #888;
        }

        .activity-form {
            margin: 0;
        }

        .activity-name {
            display: block;
            font-size: 24px;
            font-weight: 700;
        }

        .activity-price {
            display: block;
            margin-top: 5px;
            color: #9a7932;
            font-size: 20px;
        }

        .preview-card {
            padding: 24px 20px;
            border: 2px solid #9aae5c;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 5px 14px rgba(111, 100, 54, 0.12);
        }

        .preview-card .activity-icon {
            margin-bottom: 18px;
        }

        .activity-select,
        .activity-input {
            width: 100%;
            height: 54px;
            padding: 0 14px;
            border: 1px solid #b7aa7a;
            border-radius: 10px;
            background: #fff;
            color: #5f532f;
            font-size: 18px;
        }

        .activity-input {
            margin-top: 14px;
        }

        .confirm-button {
            width: 100%;
            min-height: 54px;
            margin-top: 18px;
            border: 0;
            border-radius: 10px;
            background: #9aae5c;
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
        }

        .preview-message {
            display: none;
            margin-top: 22px;
            padding: 14px;
            border-radius: 10px;
            background: #f1f5df;
            color: #617130;
            font-size: 17px;
            line-height: 1.5;
        }

        .preview-message.is-visible {
            display: block;
            margin-bottom: 22px;
        }

        .preview-message.is-warning {
            background: #fff1cf;
            color: #8a6420;
        }

        .preview-note {
            margin-top: 24px;
            color: #8f8567;
            font-size: 14px;
        }

        .secondary-link {
            display: inline-block;
            margin-top: 10px;
            color: #8c6f2d;
            font-size: 15px;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <main class="preview-page">
        <h1 class="greeting">{{ $greetingName }}，你好</h1>
        <p class="date">{{ $displayDate }}</p>

        @yield('status-message')
        @yield('activity-control')

        <div id="previewMessage" class="preview-message" role="status" aria-live="polite"></div>
        <p class="preview-note">@yield('footer-note', '此為介面試作，不會新增報到或收款紀錄。')</p>
    </main>

    @yield('scripts')
</body>
</html>
