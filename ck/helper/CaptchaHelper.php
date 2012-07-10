<?php

! defined ( 'IN_CIKE' ) ? null : '!';

/**
 * CCaptchaHelper
 * @author aray
 * @package helper
 * @access 
 */
class CaptchaHelper extends CKObject
{

    private $sessionKey = '.captcha';
    private $font = '';

    public function __construct()
    {
		$this->font = 'arial.ttf';
        if (PHP_OS == 'WINNT')
        {
            $this->font = 'c:/windows/fonts/arial.ttf';
        }
    }

    public function setFont($font)
    {
        $this->font = $font;

        return $this;
    }

    public function setSessionKey($key)
    {
        $this->sessionKey = $key;

        return $this;
    }

    public function generate($len, $width, $height)
    {
        $text = $_SESSION[$this->sessionKey] = $this->sessionKey = Util::getRandStr($len);

        $im_x = $width;
        $im_y = $height;
        $im = imagecreatetruecolor($im_x, $im_y);
        $text_c = ImageColorAllocate($im, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
        $tmpC0 = mt_rand(100, 255);
        $tmpC1 = mt_rand(100, 255);
        $tmpC2 = mt_rand(100, 255);
        $buttum_c = ImageColorAllocate($im, $tmpC0, $tmpC1, $tmpC2);
        imagefill($im, 16, 13, $buttum_c);

        $fontSize = ceil(($width - 25) / $len);

        for ($i = 0; $i < strlen($text); $i++)
        {
            $tmp = substr($text, $i, 1);
            $array = array(- 1, 1);
            $p = array_rand($array);
            $an = $array [$p] * mt_rand(1, 10); //角度
            $size = $fontSize;
            $top = ceil($height / 2 + $size / 2);
            $left = ceil(5 + $i * $size);
            imagettftext($im, $size, $an, $left, $top, $text_c, $this->font, $tmp);
        }

        $distortion_im = imagecreatetruecolor($im_x, $im_y);

        imagefill($distortion_im, 16, 13, $buttum_c);
        for ($i = 0; $i < $im_x; $i++)
        {
            for ($j = 0; $j < $im_y; $j++)
            {
                $rgb = imagecolorat($im, $i, $j);
                if ((int) ($i + 20 + sin($j / $im_y * 2 * M_PI) * 5) <= imagesx($distortion_im) && (int) ($i + 10 + sin($j / $im_y * 2 * M_PI) * 10) >= 0)
                {
                    imagesetpixel($distortion_im, (int) ($i + 4 + sin($j / $im_y * 1.85 * M_PI - M_PI * 0.32) * 4), $j, $rgb);
                }
            }
        }
        //加入干扰象素;
        $count = 50; //干扰像素的数量
        for ($i = 0; $i < $count; $i++)
        {
            $randcolor = ImageColorallocate($distortion_im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 100));
            imagesetpixel($distortion_im, mt_rand() % $im_x, mt_rand() % $im_y, $randcolor);
        }

        /**
          $rand = mt_rand(5, 30);
          $rand1 = mt_rand(15, 25);
          $rand2 = mt_rand(1, 10);
          for ($yy = $rand; $yy <= + $rand + 2; $yy++) {
          for ($px = - 80; $px <= 80; $px = $px + 0.01) {
          $x = $px / $rand1;
          if ($x != 0) {
          $y = sin($x);
          }
          $py = $y * $rand2;
          imagesetpixel($distortion_im, $px + 80, $py + $yy, $text_c);
          }
          }
          /* */
        //设置文件头;
        header("content-type: image/jpeg");
        imagejpeg($distortion_im);
        //销毁一图像,释放与image关联的内存;
        imagedestroy($distortion_im);
        imagedestroy($im);
    }

}


?>