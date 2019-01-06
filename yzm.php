<?php
/**
 * 字母+数字的验证码生成
 */
// 开启session
session_start();
//1.创建黑色画布
$image = imagecreatetruecolor(100, 30);
 
//2.为画布定义(背景)颜色
$R=mt_rand(210,255);$G=mt_rand(210,255);$B=mt_rand(210,255);
$bgcolor = imagecolorallocate($image, $R, $G, $B);
 
//3.填充颜色
imagefill($image, 0, 0, $bgcolor);
 
// 4.设置验证码内容
 
//4.1 定义验证码的内容
$content = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
 
//4.1 创建一个变量存储产生的验证码数据，便于用户提交核对
$captcha = "";
for ($i = 0; $i < 4; $i++) {
    // 字体大小
    $fontsize = 10;
    // 字体颜色
    $fontcolor = imagecolorallocate($image, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
    // 设置字体内容
    $fontcontent = substr($content, mt_rand(0, strlen($content)-1), 1);
    $captcha .= $fontcontent;
    // 显示的坐标
    $x = ($i * 100 / 4) + mt_rand(5, 10);
    $y = mt_rand(5, 10);
    // 填充内容到画布中
    imagestring($image, $fontsize, $x, $y, $fontcontent, $fontcolor);
}
$_SESSION["captcha"] = $_SESSION["yzm"] = $captcha;
 
//4.3 设置背景干扰元素 (像素点)
for ($i = 0; $i < 300; $i++) {
    /*设置填充颜色  1.图片源（对象）,2.R,3.G,4,B  后面三个参数是颜色的值 rgb*/
    $r=mt_rand(50, 200);$g=mt_rand(50, 200);$b=mt_rand(50, 200);
    $pointcolor = imagecolorallocate($image, $r, $g, $b);
    /*图片源 对象,x,y,color*/
    imagesetpixel($image, mt_rand(1, 99), mt_rand(1, 29), $pointcolor);
}
 
//4.4 设置干扰线
for ($i = 0; $i < 5; $i++) {
    $r=mt_rand(50, 200);$g=mt_rand(50, 200);$b=mt_rand(50, 200);
    $linecolor = imagecolorallocate($image, $r, $g, $b);
    $x1=mt_rand(1, 99);$y1=mt_rand(1, 29);  $x2=mt_rand(1, 99);$y2=mt_rand(1, 29);
    imageline($image, $x1, $y1, $x2, $y2, $linecolor);
}
 
//5.向浏览器输出图片头信息
header('content-type:image/png');
 
//6.输出图片到浏览器
imagepng($image);
 
//7.销毁图片
//imagedestroy($image);　