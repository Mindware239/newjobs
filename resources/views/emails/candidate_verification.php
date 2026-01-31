<!DOCTYPE html>
<html>
<head>
    <title>Verify your account</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>Hello <?= htmlspecialchars($name) ?>,</h2>
        <p>An account has been created for you at Mind Info Tech.</p>
        <p>Please click the button below to verify your email address and set your password:</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="<?= htmlspecialchars($link) ?>" style="background-color: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px;">Verify Account</a>
        </p>
        <p>This link will expire in 7 days.</p>
        <p>If you did not request this account, please ignore this email.</p>
        <p>Best regards,<br>Mind Info Tech Team</p>
    </div>
</body>
</html>