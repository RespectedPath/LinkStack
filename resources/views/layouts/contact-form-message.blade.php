<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #222;
            background: #f6f6f6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 24px;
            border-radius: 6px;
            border: 1px solid #e5e5e5;
        }

        h2 {
            color: #333;
            margin-top: 0;
        }

        .field {
            margin-bottom: 14px;
        }

        .field .label {
            display: block;
            font-size: 0.85rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            margin-bottom: 2px;
        }

        .field .value {
            display: block;
            font-size: 1rem;
        }

        .message-box {
            white-space: pre-wrap;
            word-wrap: break-word;
            background: #fafafa;
            border-left: 3px solid #3498db;
            padding: 12px 14px;
            border-radius: 0 4px 4px 0;
        }

        .footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #eee;
            font-size: 0.8rem;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>New message from your Mail Minted contact form</h2>

        <div class="field">
            <span class="label">From</span>
            <span class="value">{{ $formData['name'] }} &lt;{{ $formData['email'] }}&gt;</span>
        </div>

        <div class="field">
            <span class="label">Message</span>
            <div class="value message-box">{{ $formData['message'] }}</div>
        </div>

        <div class="footer">
            Sent via your Mail Minted page (block ID {{ $link->id }}).
            Reply directly to this e-mail to respond to {{ $formData['name'] }}.
        </div>
    </div>
</body>
</html>
