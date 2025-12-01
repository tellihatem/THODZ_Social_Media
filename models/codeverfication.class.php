<?php
//include required phpmailer files
require_once (__DIR__ . '/../PHPMailer/PHPMailer.php');
require_once (__DIR__ . '/../PHPMailer/Exception.php');
require_once (__DIR__ . '/../PHPMailer/SMTP.php');
require_once (__DIR__ . '/../config.php');
require_once (__DIR__ . '/database.class.php');

//define name spaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class CodeVerify {

	private $_link = null;
	
	function __construct(){
		$this->_link = (new Database())->connect();
	}
	
	/**
	 * Generate a secure random token
	 */
	function SetCode(){
		return bin2hex(random_bytes(32)); // 64 character secure token
	}
	
	/**
	 * Send verification email to user
	 */
	function SendCodeTo($email, $token, $fname, $lname, $subject, $url){
		$mail = new PHPMailer(true);
		
		try {
			// SMTP Configuration
			$mail->isSMTP();
			$mail->Host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;
			$mail->Username = defined('SMTP_USER') ? SMTP_USER : '';
			$mail->Password = defined('SMTP_PASS') ? SMTP_PASS : '';
			
			// Email settings
			$fromEmail = defined('SMTP_FROM') ? SMTP_FROM : (defined('SMTP_USER') ? SMTP_USER : 'noreply@thodz.com');
			$mail->setFrom($fromEmail, 'THODZ');
			$mail->addAddress($email, $fname . " " . $lname);
			$mail->isHTML(true);
			
			// Build verification URL
			$baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost:8080';
			$destination = $baseUrl . '/verify.php?email=' . urlencode($email) . '&token=' . urlencode($token);
			
			$mail->Subject = 'THODZ - ' . $subject;
			$mail->Body = $this->getEmailTemplate($fname, $destination, $subject);
			$mail->AltBody = "Hi $fname,\n\nPlease verify your email by clicking this link:\n$destination\n\nThis link expires in 24 hours.\n\nTHODZ Team";
			
			$mail->send();
			return true;
		} catch (Exception $e) {
			error_log("Email sending failed: " . $mail->ErrorInfo);
			return false;
		}
	}
	
	/**
	 * Send password reset email
	 */
	function SendPasswordReset($email, $token, $fname, $lname){
		$mail = new PHPMailer(true);
		
		try {
			$mail->isSMTP();
			$mail->Host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;
			$mail->Username = defined('SMTP_USER') ? SMTP_USER : '';
			$mail->Password = defined('SMTP_PASS') ? SMTP_PASS : '';
			
			$fromEmail = defined('SMTP_FROM') ? SMTP_FROM : (defined('SMTP_USER') ? SMTP_USER : 'noreply@thodz.com');
			$mail->setFrom($fromEmail, 'THODZ');
			$mail->addAddress($email, $fname . " " . $lname);
			$mail->isHTML(true);
			
			$baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost:8080';
			$destination = $baseUrl . '/reset-password.php?email=' . urlencode($email) . '&token=' . urlencode($token);
			
			$mail->Subject = 'THODZ - Reset Your Password';
			$mail->Body = $this->getPasswordResetTemplate($fname, $destination);
			$mail->AltBody = "Hi $fname,\n\nYou requested to reset your password. Click this link:\n$destination\n\nThis link expires in 1 hour.\n\nIf you didn't request this, please ignore this email.\n\nTHODZ Team";
			
			$mail->send();
			return true;
		} catch (Exception $e) {
			error_log("Password reset email failed: " . $mail->ErrorInfo);
			return false;
		}
	}
	
	/**
	 * Get beautiful HTML email template for verification
	 */
	private function getEmailTemplate($fname, $verifyUrl, $subject){
		return '
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
		</head>
		<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen, Ubuntu, sans-serif; background-color: #f0f2f5;">
			<table role="presentation" style="width: 100%; border-collapse: collapse;">
				<tr>
					<td align="center" style="padding: 40px 0;">
						<table role="presentation" style="width: 100%; max-width: 600px; border-collapse: collapse; background-color: #ffffff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
							<!-- Header -->
							<tr>
								<td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, #1877f2 0%, #42b72a 100%); border-radius: 12px 12px 0 0;">
									<h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700;">THODZ</h1>
									<p style="margin: 10px 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Connect with friends and the world</p>
								</td>
							</tr>
							
							<!-- Content -->
							<tr>
								<td style="padding: 40px;">
									<h2 style="margin: 0 0 20px; color: #1c1e21; font-size: 24px; font-weight: 600;">Hi ' . htmlspecialchars($fname) . '! 👋</h2>
									<p style="margin: 0 0 20px; color: #65676b; font-size: 16px; line-height: 1.6;">
										Welcome to THODZ! We\'re excited to have you join our community. Please verify your email address to get started.
									</p>
									
									<!-- Button -->
									<table role="presentation" style="width: 100%; border-collapse: collapse;">
										<tr>
											<td align="center" style="padding: 20px 0;">
												<a href="' . htmlspecialchars($verifyUrl) . '" style="display: inline-block; padding: 14px 40px; background-color: #1877f2; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 8px; box-shadow: 0 2px 4px rgba(24,119,242,0.3);">
													Verify Email Address
												</a>
											</td>
										</tr>
									</table>
									
									<p style="margin: 20px 0 0; color: #65676b; font-size: 14px; line-height: 1.6;">
										This link will expire in <strong>24 hours</strong>. If you didn\'t create an account on THODZ, you can safely ignore this email.
									</p>
									
									<!-- Divider -->
									<hr style="margin: 30px 0; border: none; border-top: 1px solid #e4e6e9;">
									
									<p style="margin: 0; color: #8a8d91; font-size: 12px;">
										If the button doesn\'t work, copy and paste this link into your browser:<br>
										<a href="' . htmlspecialchars($verifyUrl) . '" style="color: #1877f2; word-break: break-all;">' . htmlspecialchars($verifyUrl) . '</a>
									</p>
								</td>
							</tr>
							
							<!-- Footer -->
							<tr>
								<td style="padding: 20px 40px; background-color: #f0f2f5; border-radius: 0 0 12px 12px; text-align: center;">
									<p style="margin: 0; color: #8a8d91; font-size: 12px;">
										© ' . date('Y') . ' THODZ. All rights reserved.<br>
										This is an automated message, please do not reply.
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</body>
		</html>';
	}
	
	/**
	 * Get HTML template for password reset
	 */
	private function getPasswordResetTemplate($fname, $resetUrl){
		return '
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
		</head>
		<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen, Ubuntu, sans-serif; background-color: #f0f2f5;">
			<table role="presentation" style="width: 100%; border-collapse: collapse;">
				<tr>
					<td align="center" style="padding: 40px 0;">
						<table role="presentation" style="width: 100%; max-width: 600px; border-collapse: collapse; background-color: #ffffff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
							<!-- Header -->
							<tr>
								<td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, #1877f2 0%, #f02849 100%); border-radius: 12px 12px 0 0;">
									<h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700;">THODZ</h1>
									<p style="margin: 10px 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Password Reset Request</p>
								</td>
							</tr>
							
							<!-- Content -->
							<tr>
								<td style="padding: 40px;">
									<h2 style="margin: 0 0 20px; color: #1c1e21; font-size: 24px; font-weight: 600;">Hi ' . htmlspecialchars($fname) . ',</h2>
									<p style="margin: 0 0 20px; color: #65676b; font-size: 16px; line-height: 1.6;">
										We received a request to reset your password. Click the button below to create a new password.
									</p>
									
									<!-- Button -->
									<table role="presentation" style="width: 100%; border-collapse: collapse;">
										<tr>
											<td align="center" style="padding: 20px 0;">
												<a href="' . htmlspecialchars($resetUrl) . '" style="display: inline-block; padding: 14px 40px; background-color: #f02849; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 8px; box-shadow: 0 2px 4px rgba(240,40,73,0.3);">
													Reset Password
												</a>
											</td>
										</tr>
									</table>
									
									<p style="margin: 20px 0 0; color: #65676b; font-size: 14px; line-height: 1.6;">
										This link will expire in <strong>1 hour</strong>. If you didn\'t request a password reset, please ignore this email or contact support if you have concerns.
									</p>
									
									<!-- Divider -->
									<hr style="margin: 30px 0; border: none; border-top: 1px solid #e4e6e9;">
									
									<p style="margin: 0; color: #8a8d91; font-size: 12px;">
										If the button doesn\'t work, copy and paste this link into your browser:<br>
										<a href="' . htmlspecialchars($resetUrl) . '" style="color: #1877f2; word-break: break-all;">' . htmlspecialchars($resetUrl) . '</a>
									</p>
								</td>
							</tr>
							
							<!-- Footer -->
							<tr>
								<td style="padding: 20px 40px; background-color: #f0f2f5; border-radius: 0 0 12px 12px; text-align: center;">
									<p style="margin: 0; color: #8a8d91; font-size: 12px;">
										© ' . date('Y') . ' THODZ. All rights reserved.<br>
										This is an automated message, please do not reply.
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</body>
		</html>';
	}
}