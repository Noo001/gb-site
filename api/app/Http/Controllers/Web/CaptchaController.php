<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class CaptchaController extends Controller
{
    public function show(): Response
    {
        $code = $this->generateCode();
        session(['captcha_code' => $code]);

        $image = imagecreatetruecolor(150, 50);
        $bg = imagecolorallocate($image, 245, 245, 245);
        imagefill($image, 0, 0, $bg);

        // Шум — точки
        for ($i = 0; $i < 200; $i++) {
            $color = imagecolorallocate($image, mt_rand(120, 200), mt_rand(120, 200), mt_rand(120, 200));
            imagesetpixel($image, mt_rand(0, 149), mt_rand(0, 49), $color);
        }

        // Шум — линии
        for ($i = 0; $i < 5; $i++) {
            $color = imagecolorallocate($image, mt_rand(150, 220), mt_rand(150, 220), mt_rand(150, 220));
            imageline($image, mt_rand(0, 149), mt_rand(0, 49), mt_rand(0, 149), mt_rand(0, 49), $color);
        }

        // Символы
        $chars = str_split($code);
        $x = 15;
        foreach ($chars as $char) {
            $color = imagecolorallocate($image, mt_rand(20, 80), mt_rand(20, 80), mt_rand(20, 80));
            $size = mt_rand(3, 5);
            $y = mt_rand(25, 40);
            imagestring($image, $size, $x, $y, $char, $color);
            $x += 25;
        }

        ob_start();
        imagepng($image);
        $content = ob_get_clean();
        imagedestroy($image);

        return response($content, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    private function generateCode(int $length = 5): string
    {
        $pool = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $pool[mt_rand(0, strlen($pool) - 1)];
        }
        return $code;
    }
}
