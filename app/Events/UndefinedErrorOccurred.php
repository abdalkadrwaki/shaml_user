<?php

namespace App\Events;

use Exception;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UndefinedErrorOccurred
{
    use Dispatchable, SerializesModels;

    public string $errorMessage;
    public string $errorCode;
    protected array $sensitivePatterns = [
        '/password\s*=\s*.+/',
        '/\bapi_key\b.*/',
        '/database\.password/',
    ];

    /**
     * إنشاء نسخة جديدة من الحدث مع تعقيم المعلومات الحساسة
     *
     * @param \Exception $exception
     * @return void
     */
    public function __construct(Exception $exception)
    {
        $message = $exception->getMessage();
        $this->errorMessage = $this->sanitizeErrorMessage($message);
        $this->errorCode = $this->generateErrorCode($exception);
    }

    /**
     * تعقيم رسالة الخطأ من المعلومات الحساسة
     */
    private function sanitizeErrorMessage(string $message): string
    {
        return preg_replace(
            $this->sensitivePatterns,
            '[REDACTED]',
            $message
        );
    }

    /**
     * توليد رمز خطأ آمن للعرض
     */
    private function generateErrorCode(Exception $exception): string
    {
        return 'ERR-' . md5(
            $exception->getFile() .
            $exception->getLine() .
            $exception->getCode() .
            now()->timestamp
        );
    }
}
