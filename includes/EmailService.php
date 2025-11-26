<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['MAIL_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'];
        $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
        $this->mailer->Port = $_ENV['MAIL_PORT'];
        
        // Default settings
        $this->mailer->isHTML(true);
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
    }

    public function sendPasswordResetEmail($email, $username, $resetLink) {
        try {
            $this->mailer->addAddress($email);
            $this->mailer->Subject = 'Reset Your Password - Pag-IBIG Remittance Generator';
            
            // Email body
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <img src='https://upload.wikimedia.org/wikipedia/commons/5/5b/Pag-IBIG.svg' alt='PAG-IBIG Logo' style='max-width: 150px; margin: 20px 0;'>
                <h2 style='color: #005594;'>Password Reset Request</h2>
                <p>Hello {$username},</p>
                <p>We received a request to reset your password for your Pag-IBIG Remittance Generator account. Click the button below to reset your password:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetLink}' style='background-color: #005594; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                </p>
                <p>If you didn't request this password reset, you can safely ignore this email. The link will expire in 1 hour.</p>
                <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666;'>
                    This is an automated email. Please do not reply to this message.
                </p>
            </div>";
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $body));
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
}
