<?php

namespace Tests\Unit;

use App\Services\Documents\ResumePhoto;
use Tests\TestCase;

class ResumePhotoTest extends TestCase
{
    public function test_cover_keeps_the_center_and_crops_the_edges(): void
    {
        $image = imagecreatetruecolor(300, 100);
        $red = imagecolorallocate($image, 220, 20, 20);
        $blue = imagecolorallocate($image, 20, 20, 220);
        imagefilledrectangle($image, 0, 0, 299, 99, $red);
        imagefilledrectangle($image, 140, 0, 160, 99, $blue);

        $cropped = (new ResumePhoto)->cover($image, 100, 100);
        $edge = imagecolorat($cropped, 2, 50);
        $center = imagecolorat($cropped, 50, 50);

        $this->assertGreaterThan(180, ($edge >> 16) & 255);
        $this->assertLessThan(40, ($center >> 16) & 255);
        $this->assertGreaterThan(180, $center & 255);
        $this->assertSame(100, imagesx($cropped));
        $this->assertSame(100, imagesy($cropped));

        imagedestroy($image);
        imagedestroy($cropped);
    }
}
