<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class ErrorLog extends Model
{
    protected $guarded = [];

    public static function write(Throwable $exception, ?Request $request = null): void
    {
        $message = self::redact($exception->getMessage());

        self::query()->create([
            'level' => 'error',
            'exception' => class_basename($exception),
            'message' => Str::limit($message !== '' ? $message : 'Erreur interne', 480, ''),
            'file' => basename($exception->getFile()),
            'line' => $exception->getLine(),
            'url' => $request ? Str::limit($request->path(), 180, '') : null,
            'user_id' => $request?->user()?->id,
        ]);
    }

    public static function redact(string $message): string
    {
        $message = preg_replace('/sk-[A-Za-z0-9_\-]+/', '[redacted]', $message) ?? $message;
        $message = preg_replace('/Bearer\s+[A-Za-z0-9\-_\.]+/i', 'Bearer [redacted]', $message) ?? $message;

        return $message;
    }
}
