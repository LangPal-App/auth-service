<?php

use Intervention\Image\Facades\Image;

function generateRandomNumbers(int $length)
{
    return env('APP_ENV') === 'production'
            ? substr( str_shuffle("01234567890123456789"), 0, $length)
            : '123456';
}

function saveInputFile($requestImage, $dir_path)
{
    $image_name = time() . '_' . str_replace(' ', '_', $requestImage->getClientOriginalName());
    if(!is_dir($dir_path)) {
        mkdir($dir_path, 0777, true);
    }
    return saveThumb($requestImage,$dir_path . '/' . $image_name, 600, 600);
}

function saveThumb($requestImage, $imagePath, int $x=1200, int $y=1200)
{
    $destinationPath = public_path() . '/storage';

    $extPos = strrpos($imagePath, '.');
    if ($extPos !== false) {
        $webpPath = substr($imagePath, 0, $extPos) . '.webp';
    } else {
        $webpPath = $imagePath . '.webp';
    }

    $imageSize = getimagesize($requestImage);
    $imageWidth = $imageSize[0];
    $imageHeight = $imageSize[1];
    $mime = $imageSize['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($requestImage);
            break;
        case 'image/png':
            $image = imagecreatefrompng($requestImage);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($requestImage);
            break;
        default:
            throw new Exception('Unsupported image type');
    }

    if ($imageWidth > $x || $imageHeight > $y) {
        $ratio = min($x / $imageWidth, $y / $imageHeight);
        $newWidth = (int)($imageWidth * $ratio);
        $newHeight = (int)($imageHeight * $ratio);

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagecolortransparent($resized, imagecolorallocatealpha($resized, 0, 0, 0, 127));
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled(
            $resized,
            $image,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $imageWidth, $imageHeight
        );
        imagedestroy($image);
        $image = $resized;
    }

    if (!function_exists('imagewebp')) {
        throw new Exception('WebP support not available in GD');
    }

    $destFullPath = $destinationPath . '/' . $webpPath;
    imagewebp($image, $destFullPath, 90);
    imagedestroy($image);

    return $webpPath;
}