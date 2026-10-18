<?php

namespace App\Services\Documents;

use App\Models\Resume;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ResumePhoto
{
    public const LAYOUTS = [
        'experimente' => 'corporate',
        'etudiant' => 'blue',
        'classique' => 'classic',
        'international' => 'elegant',
        'moderne' => 'modern',
        'minimaliste' => 'minimal',
        'administratif' => 'executive',
        'technique' => 'technical',
    ];

    public const SHAPES = [
        'corporate' => 'portrait',
        'blue' => 'portrait',
        'classic' => 'square',
        'elegant' => 'circle',
        'modern' => 'square',
        'minimal' => 'circle',
        'executive' => 'circle',
        'technical' => 'square',
    ];

    public static function layout(string $slug): string
    {
        return self::LAYOUTS[$slug] ?? 'modern';
    }

    public static function shape(string $layout): string
    {
        return self::SHAPES[$layout] ?? 'square';
    }

    /**
     * @return array{fill: string, ink: string}
     */
    public static function monogramColors(string $layout, string $primary): array
    {
        return match ($layout) {
            'elegant' => ['fill' => $primary, 'ink' => '#FFFFFF'],
            'minimal' => ['fill' => '#1C1917', 'ink' => '#FFFFFF'],
            'executive' => ['fill' => '#FFFFFF', 'ink' => '#1C1917'],
            default => ['fill' => '#FFFFFF', 'ink' => '#1C1917'],
        };
    }

    public static function backdrop(string $layout, string $primary): string
    {
        return match ($layout) {
            'corporate', 'blue', 'executive' => $primary,
            'elegant' => '#F4EFE8',
            default => '#FFFFFF',
        };
    }

    public function store(Resume $resume, UploadedFile $file): string
    {
        $binary = file_get_contents($file->getRealPath());
        $image = $this->load($binary ?: '', $file->getMimeType() ?: '', $file->getRealPath());
        $image = $this->limit($image, 1400);

        $path = $this->originalPath($resume);
        $this->delete($resume->photo_path);
        Storage::disk('local')->put($path, $this->encodeJpeg($image, 86));
        imagedestroy($image);

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! is_string($path) || ! preg_match('#^resume-photos/(\d+)/(\d+)\.jpg$#', $path, $match)) {
            return;
        }

        $disk = Storage::disk('local');
        $directory = 'resume-photos/'.$match[1];
        $disk->delete($path);

        if (! $disk->directoryExists($directory)) {
            return;
        }

        foreach ($disk->files($directory) as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);

            if ($name === $match[2] || str_starts_with($name, $match[2].'-')) {
                $disk->delete($file);
            }
        }
    }

    public function copy(?string $path, Resume $target): ?string
    {
        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        $destination = $this->originalPath($target);
        Storage::disk('local')->put($destination, Storage::disk('local')->get($path));

        return $destination;
    }

    public function dataUri(?string $path, string $shape, string $backdrop): ?string
    {
        $rendered = $this->cached($path, $shape, $backdrop);

        if ($rendered === null) {
            return null;
        }

        return 'data:'.$rendered['mime'].';base64,'.base64_encode($rendered['bytes']);
    }

    public function monogramUri(string $initials, string $shape, string $layout, string $primary): string
    {
        $shape = in_array($shape, ['square', 'portrait', 'circle'], true) ? $shape : 'square';
        [$width, $height] = $shape === 'portrait' ? [360, 480] : [400, 400];
        $colors = self::monogramColors($layout, $primary);
        $image = imagecreatetruecolor($width, $height);
        [$red, $green, $blue] = $this->rgb(self::backdrop($layout, $primary));
        $background = imagecolorallocate($image, $red, $green, $blue);
        imagefilledrectangle($image, 0, 0, $width, $height, $background);
        [$red, $green, $blue] = $this->rgb($colors['fill']);
        $fill = imagecolorallocate($image, $red, $green, $blue);

        if ($shape === 'circle') {
            imagefilledellipse($image, (int) ($width / 2), (int) ($height / 2), $width - 2, $height - 2, $fill);
        } else {
            imagefilledrectangle($image, 0, 0, $width, $height, $fill);
        }

        [$red, $green, $blue] = $this->rgb($colors['ink']);
        $ink = imagecolorallocate($image, $red, $green, $blue);
        $text = mb_substr($initials !== '' ? $initials : 'CV', 0, 2);
        $font = $this->fontPath();

        if ($font !== null) {
            $size = (int) round(min($width, $height) * 0.28);
            $box = imagettfbbox($size, 0, $font, $text);
            $textWidth = abs($box[2] - $box[0]);
            $textHeight = abs($box[7] - $box[1]);
            imagettftext(
                $image,
                $size,
                0,
                (int) (($width - $textWidth) / 2),
                (int) (($height + $textHeight) / 2),
                $ink,
                $font,
                $text,
            );
        } else {
            imagestring($image, 5, (int) ($width / 2) - 16, (int) ($height / 2) - 8, $text, $ink);
        }

        $bytes = $this->encodePng($image);
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($bytes);
    }

    public function temporarySquare(Resume $resume): ?string
    {
        $rendered = $this->cached($resume->photo_path, 'square', '#FFFFFF');

        if ($rendered === null) {
            return null;
        }

        $temp = tempnam(sys_get_temp_dir(), 'cvora-photo');

        if ($temp === false) {
            return null;
        }

        $path = $temp.'.jpg';
        rename($temp, $path);
        file_put_contents($path, $rendered['bytes']);

        return $path;
    }

    /**
     * @return array{mime: string, bytes: string}|null
     */
    public function cached(?string $path, string $shape, string $backdrop): ?array
    {
        if (! is_string($path) || ! preg_match('#^resume-photos/\d+/\d+\.jpg$#', $path) || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        $shape = in_array($shape, ['square', 'portrait', 'circle'], true) ? $shape : 'square';
        $backdrop = $this->hex($backdrop) ?? '#FFFFFF';
        $cache = preg_replace('/\.jpg$/', '-'.$shape.'-'.substr($backdrop, 1).($shape === 'circle' ? '.png' : '.jpg'), $path);
        $disk = Storage::disk('local');

        if ($disk->exists($cache)) {
            return [
                'mime' => str_ends_with($cache, '.png') ? 'image/png' : 'image/jpeg',
                'bytes' => $disk->get($cache),
            ];
        }

        $source = @imagecreatefromstring($disk->get($path));

        if (! $source instanceof GdImage) {
            return null;
        }

        [$width, $height] = $shape === 'portrait' ? [360, 480] : [400, 400];
        $cropped = $this->cover($source, $width, $height);
        imagedestroy($source);

        if ($shape === 'circle') {
            $circled = $this->circle($cropped, $backdrop);
            imagedestroy($cropped);
            $bytes = $this->encodePng($circled);
            imagedestroy($circled);
            $mime = 'image/png';
        } else {
            $bytes = $this->encodeJpeg($cropped, 86);
            imagedestroy($cropped);
            $mime = 'image/jpeg';
        }

        $disk->put($cache, $bytes);

        return ['mime' => $mime, 'bytes' => $bytes];
    }

    public function cover(GdImage $source, int $targetWidth, int $targetHeight): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = max($targetWidth / max($width, 1), $targetHeight / max($height, 1));
        $cropWidth = max(1, (int) round($targetWidth / $scale));
        $cropHeight = max(1, (int) round($targetHeight / $scale));
        $originX = (int) max(0, ($width - $cropWidth) / 2);
        $originY = (int) max(0, ($height - $cropHeight) / 2);
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($target, 255, 255, 255);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $white);
        imagecopyresampled($target, $source, 0, 0, $originX, $originY, $targetWidth, $targetHeight, $cropWidth, $cropHeight);

        return $target;
    }

    private function fontPath(): ?string
    {
        foreach ([
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            'C:\\Windows\\Fonts\\arialbd.ttf',
        ] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function originalPath(Resume $resume): string
    {
        return 'resume-photos/'.$resume->user_id.'/'.$resume->id.'.jpg';
    }

    private function load(string $binary, string $mime, string $path): GdImage
    {
        if (strlen($binary) > 5 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'photo' => 'La photo dépasse 5 Mo.',
            ]);
        }

        $image = @imagecreatefromstring($binary);

        if (! $image instanceof GdImage) {
            throw ValidationException::withMessages([
                'photo' => 'Cette image ne peut pas être lue. Utilisez un fichier JPEG, PNG ou WebP.',
            ]);
        }

        if (imagesx($image) < 80 || imagesy($image) < 80) {
            imagedestroy($image);

            throw ValidationException::withMessages([
                'photo' => 'La photo est trop petite. Utilisez une image d\'au moins 80 pixels.',
            ]);
        }

        if (imagesx($image) > 8000 || imagesy($image) > 8000) {
            imagedestroy($image);

            throw ValidationException::withMessages([
                'photo' => 'La photo est trop grande. Réduisez-la avant de l\'envoyer.',
            ]);
        }

        if (str_contains($mime, 'jpeg') && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $orientation = (int) ($exif['Orientation'] ?? 1);
            $rotated = match ($orientation) {
                3 => imagerotate($image, 180, 0),
                6 => imagerotate($image, -90, 0),
                8 => imagerotate($image, 90, 0),
                default => $image,
            };

            if ($rotated instanceof GdImage && $rotated !== $image) {
                imagedestroy($image);
                $image = $rotated;
            }
        }

        return $image;
    }

    private function limit(GdImage $image, int $max): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= $max) {
            return $image;
        }

        $scale = $max / $longest;
        $target = imagecreatetruecolor(max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale)));
        $white = imagecolorallocate($target, 255, 255, 255);
        imagefilledrectangle($target, 0, 0, imagesx($target), imagesy($target), $white);
        imagecopyresampled($target, $image, 0, 0, 0, 0, imagesx($target), imagesy($target), $width, $height);
        imagedestroy($image);

        return $target;
    }

    private function circle(GdImage $source, string $hex): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $output = imagecreatetruecolor($width, $height);
        [$red, $green, $blue] = $this->rgb($hex);
        $fill = imagecolorallocate($output, $red, $green, $blue);
        imagefilledrectangle($output, 0, 0, $width, $height, $fill);
        $radius = (min($width, $height) / 2) - 1;
        $centerX = $width / 2;
        $centerY = $height / 2;
        $radiusSquared = $radius * $radius;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $dx = $x - $centerX;
                $dy = $y - $centerY;

                if (($dx * $dx) + ($dy * $dy) <= $radiusSquared) {
                    imagesetpixel($output, $x, $y, imagecolorat($source, $x, $y));
                }
            }
        }

        return $output;
    }

    private function encodeJpeg(GdImage $image, int $quality): string
    {
        ob_start();
        imagejpeg($image, null, $quality);

        return (string) ob_get_clean();
    }

    private function encodePng(GdImage $image): string
    {
        ob_start();
        imagepng($image, null, 6);

        return (string) ob_get_clean();
    }

    private function hex(string $value): ?string
    {
        $value = strtoupper(trim($value));

        return preg_match('/^#[0-9A-F]{6}$/', $value) ? $value : null;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function rgb(string $hex): array
    {
        $hex = $this->hex($hex) ?? '#FFFFFF';

        return [
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        ];
    }
}
