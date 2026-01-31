<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class CaptchaController extends BaseController
{
    public function __construct()
    {
        // Skip parent constructor for captcha to avoid any database calls
        // Parent would call loadCurrentUser() which might fail
    }
    
    public function generate(Request $request, Response $response): void
    {
        try {
            // Clear any output buffers FIRST
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Disable any error display
            ini_set('display_errors', '0');
            
            // Ensure session is started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // If GD is missing, log error with php.ini location
            if (!extension_loaded('gd')) {
                $iniFile = php_ini_loaded_file();
                error_log("CAPTCHA Error: GD extension not loaded. php.ini location: " . ($iniFile ?: 'unknown'));
                http_response_code(500);
                header('Content-Type: text/plain; charset=utf-8');
                echo "ERROR: GD extension not enabled.\n";
                echo "php.ini location: " . ($iniFile ?: 'unknown') . "\n";
                echo "To fix: Edit php.ini and uncomment: extension=gd\n";
                echo "Then restart Apache/XAMPP.\n";
                echo "\nCheck: http://localhost:8000/check_gd.php for details";
                exit;
            }
            
            if (!function_exists('imagecreatetruecolor')) {
                error_log("CAPTCHA Error: imagecreatetruecolor function not available");
                http_response_code(500);
                header('Content-Type: text/plain; charset=utf-8');
                echo "ERROR: GD functions not available.\n";
                echo "Please check GD extension installation.";
                exit;
            }

            // Generate random 6-character code
            $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }

            // Store in session
            $_SESSION['admin_captcha'] = $code;

            // Create image
            $width = 150;
            $height = 50;
            $image = imagecreatetruecolor($width, $height);

            // Colors
            $bgColor = imagecolorallocate($image, 255, 255, 255);
            $textColors = [
                imagecolorallocate($image, 0, 0, 0),
                imagecolorallocate($image, 59, 130, 246), // Blue
                imagecolorallocate($image, 34, 197, 94),  // Green
                imagecolorallocate($image, 239, 68, 68),  // Red
                imagecolorallocate($image, 147, 51, 234), // Purple
            ];
            $lineColor = imagecolorallocate($image, 200, 200, 200);

            // Fill background
            imagefill($image, 0, 0, $bgColor);

            // Add noise lines
            for ($i = 0; $i < 5; $i++) {
                imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
            }

            // Add noise dots
            for ($i = 0; $i < 50; $i++) {
                imagesetpixel($image, rand(0, $width), rand(0, $height), $textColors[rand(0, count($textColors) - 1)]);
            }

            // Add text
            $fontSize = 20;
            $x = 15;
            $y = 35;

            for ($i = 0; $i < strlen($code); $i++) {
                $char = $code[$i];
                $angle = rand(-15, 15);
                $color = $textColors[rand(0, count($textColors) - 1)];
                
                // Use built-in font (you can use imageloadfont for custom fonts)
                imagestring($image, 5, $x, $y - 10, $char, $color);
                
                // Add rotation effect using imagettftext if you have fonts
                // For now, using imagestring with slight position variation
                $x += 22;
                $y += rand(-3, 3);
            }

            // Output image - MUST be before any other output
            header('Content-Type: image/png');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Pragma: no-cache');
            
            // Send image
            imagepng($image);
            imagedestroy($image);
            
            // Exit immediately to prevent any other output
            exit(0);
        } catch (\Throwable $e) {
            // Log error but don't output anything that would break image
            error_log("Captcha generation error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            
            // Clear buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Return error image
            http_response_code(500);
            header('Content-Type: image/png');
            
            // Create a simple error image
            $errorImage = imagecreatetruecolor(150, 50);
            $bg = imagecolorallocate($errorImage, 255, 255, 255);
            $textColor = imagecolorallocate($errorImage, 255, 0, 0);
            imagefill($errorImage, 0, 0, $bg);
            imagestring($errorImage, 3, 10, 15, 'ERROR', $textColor);
            imagepng($errorImage);
            imagedestroy($errorImage);
            exit(0);
        }
    }
}

