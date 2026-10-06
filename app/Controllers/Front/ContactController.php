<?php

declare(strict_types=1);

namespace App\Controllers\Front;


use App\Core\Request; // Assuming you have a Request object to get data

class ContactController
{
    /**
     * Handles the form submission (POST /contact)
     *
     * @param Request $request The incoming HTTP request object.
     * @return void
     */
    public static function submitForm(Request $request): void
    {
        // 1. Basic Security Check (Honeypot + Captcha)
        $data = $request->all();

        // Check the hidden anti-spam field
        if (!empty($data['_hp_email'])) {
            header("Location: /contact?status=error&msg=Bot detected.");
            exit(); 
        }

        // Validate Captcha
        $user_captcha = (int)($data['captcha'] ?? 0);
        $correct_captcha = (int)($_SESSION['captcha_result'] ?? -1);

        if ($user_captcha !== $correct_captcha) {
            header("Location: /contact?status=error&msg=Incorrect security answer. Please try again.");
            exit();
        }

        // Clear captcha after use
        unset($_SESSION['captcha_result']);
        
        // 2. Data Retrieval and Validation
        
        $name    = $data['name'] ?? null;
        $email   = $data['email'] ?? null;
        $subject = $data['subject'] ?? null;
        $message = $data['message'] ?? null;

        // Basic validation
        if (
            empty($name) || 
            !filter_var($email, FILTER_VALIDATE_EMAIL) || 
            empty($subject) || 
            empty($message)
        ) {
            header("Location: /contact?status=error&msg=Invalid input fields provided.");
            exit();
        }

        // Sanitize data before use (important for email content)
        $name = htmlspecialchars(strip_tags($name));
        $subject = htmlspecialchars(strip_tags($subject));
        $message = htmlspecialchars(strip_tags($message));
        
        // 3. Process the Data (Send an Email)
        
        // gm@jobsence.com, plus ADMIN_MAIL / OWNER_MAIL from .env if set
        $to = \App\Helpers\AdminMail::to();
        $site_name = $_ENV['APP_NAME'] ?? "Jobsence";

        $email_subject = "New Contact Submission from {$site_name}: " . $subject;
        
        // Build the email body (Professional HTML)
        $email_body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
                .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .email-header { background-color: #2563eb; color: #ffffff; padding: 20px; text-align: center; }
                .email-header h1 { margin: 0; font-size: 24px; font-weight: 600; }
                .email-content { padding: 30px; }
                .info-item { margin-bottom: 20px; border-bottom: 1px solid #eeeeee; padding-bottom: 15px; }
                .info-item:last-child { border-bottom: none; }
                .label { display: block; font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; margin-bottom: 5px; }
                .value { font-size: 16px; color: #1f2937; }
                .message-box { background-color: #f9fafb; padding: 15px; border-radius: 6px; border-left: 4px solid #2563eb; margin-top: 10px; }
                .email-footer { background-color: #f3f4f6; padding: 15px; text-align: center; font-size: 12px; color: #6b7280; }
                .highlight { color: #2563eb; font-weight: 500; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>New Contact Inquiry</h1>
                </div>
                <div class='email-content'>
                    <p>Hello Admin,</p>
                    <p>You have received a new message via the <strong>{$site_name}</strong> contact form.</p>
                    
                    <div class='info-item'>
                        <span class='label'>Name</span>
                        <div class='value'>{$name}</div>
                    </div>
                    
                    <div class='info-item'>
                        <span class='label'>Email Address</span>
                        <div class='value'><a href='mailto:{$email}' class='highlight'>{$email}</a></div>
                    </div>
                    
                    <div class='info-item'>
                        <span class='label'>Subject</span>
                        <div class='value'>{$subject}</div>
                    </div>
                    
                    <div class='info-item'>
                        <span class='label'>Message</span>
                        <div class='value message-box'>" . nl2br($message) . "</div>
                    </div>
                </div>
                <div class='email-footer'>
                    &copy; " . date('Y') . " {$site_name}. All rights reserved.<br>
                    This email was sent from the contact form on your website.
                </div>
            </div>
        </body>
        </html>";
        
        // Use MailService to send via SMTP
        $mail_success = \App\Services\MailService::sendEmail($to, $email_subject, $email_body, null, null);

        // 4. Redirect based on success/failure

        if ($mail_success) {
            header("Location: /contact?status=success");
        } else {
            error_log("CONTACT FORM ERROR: Mail failed to send to {$to} from {$email}.");
            header("Location: /contact?status=error&msg=System error, try again later.");
        }
    }

    /**
     * Renders the Contact page view (GET /contact)
     * * @return void
     */
    public static function index(): void
    {
        // Generate a simple math captcha
        $num1 = rand(1, 10);
        $num2 = rand(1, 9);
        $_SESSION['captcha_result'] = $num1 + $num2;
        $captcha_question = "What is {$num1} + {$num2}?";

        $viewPath = dirname(dirname(dirname(__DIR__))) . '/resources/views/contact.php';
        require_once $viewPath;
    }
}