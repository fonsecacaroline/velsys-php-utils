<?php 
	declare(strict_types=1); header('Content-Type: application/json; charset=utf-8');

	/** JSON response helper */
	function jsonResponse(bool $success, string $message): void { 
		echo json_encode([ 
			'success' => $success, 
			'message' => $message 
		]);
		exit; 
	} 

	/** captcha validation example */ 
	function validateCaptcha(string $token): bool { 
		if (empty($token)) {
			return false; 
		}
		return true; 
	}
	
	/** Request limiter example */ 
		class RequestLimiter {
		public function check(string $identifier): bool {
			/** Example implementation */
			return true; 
		}
		public function hit(string $identifier): void {
			/** Store failed attempt */ 
		}
		public function reset(string $identifier): void {
			/** Clear failed attempts */ 
		}
	}


	/** Mail service example */
	class MailService {
		public function send(array $data): bool {
			/** Replace with your own implementation */ return true; 
		}
		  
	}

	$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
	$limiter = new RequestLimiter();
	
	if (!$limiter->check($ip)) {
		jsonResponse(false, 'Too many attempts. Please try again later.');
	}
	
	$captchaToken = $_POST['captcha_token'] ?? '';

	if (!validateCaptcha($captchaToken)) {
		$limiter->hit($ip);
		jsonResponse(false, 'Invalid captcha.');
	}
	
	$formData = [
		'name' => trim($_POST['name'] ?? ''),
		'email' => trim($_POST['email'] ?? ''),
		'message' => trim($_POST['message'] ?? '')
	];

	try {
		$mailer = new MailService();
		$mailer->send($formData);
		$limiter->reset($ip);
		jsonResponse(true, 'Message sent successfully.'); 
	}


	catch (Throwable $e) {
		$limiter->hit($ip);
		/** Never expose internal errors in production */ 
		jsonResponse(false, 'Unable to process request.'); 
	}
?>