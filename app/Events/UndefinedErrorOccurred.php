<?php

namespace App\Events;

use Throwable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UndefinedErrorOccurred
{
    use Dispatchable, SerializesModels;

    /**
     * The uncaught exception or error that occurred.
     *
     * @var \Throwable
     */
    public Throwable $exception;

    /**
     * The ID of the authenticated user (if any).
     *
     * @var int|null
     */
    public ?int $userId;

    /**
     * The email of the authenticated user (if any).
     *
     * @var string|null
     */
    public ?string $userEmail;

    /**
     * The full URL of the request.
     *
     * @var string
     */
    public string $url;

    /**
     * The HTTP method used for the request (GET, POST, etc.).
     *
     * @var string
     */
    public string $method;

    /**
     * The query parameters of the request.
     *
     * @var array
     */
    public array $queryParams;

    /**
     * The timestamp when the error occurred.
     *
     * @var string
     */
    public string $timestamp;

    /**
     * The file where the error occurred.
     *
     * @var string
     */
    public string $file;

    /**
     * The line number where the error occurred.
     *
     * @var int
     */
    public int $line;

    /**
     * The message of the error.
     *
     * @var string
     */
    public string $errorMessage;

    /**
     * The stack trace of the error.
     *
     * @var string
     */
    public string $stackTrace;

    /**
     * The environment the application is running in (local, production, etc.).
     *
     * @var string
     */
    public string $environment;

    /**
     * The type of error (e.g., Database Error, General Error).
     *
     * @var string
     */
    public string $errorType;

    /**
     * A custom message for the error, depending on its type.
     *
     * @var string
     */
    public string $customMessage;

    /**
     * Create a new event instance.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
        $this->userId = auth()->check() ? auth()->id() : null;
        $this->userEmail = auth()->check() ? auth()->user()->email : null;
        $this->url = request()->fullUrl();
        $this->method = request()->method();
        $this->queryParams = request()->query();
        $this->timestamp = now()->toDateTimeString();
        $this->file = $exception->getFile();
        $this->line = $exception->getLine();
        $this->errorMessage = $exception->getMessage();
        $this->stackTrace = $exception->getTraceAsString();
        $this->environment = app()->environment();
        $this->errorType = $this->getErrorType($exception);
        $this->customMessage = $this->getCustomMessage($exception);
    }

    /**
     * Determine the type of the error.
     *
     * @param \Throwable $exception
     * @return string
     */
    protected function getErrorType(Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Database\QueryException) {
            return 'Database Error';
        }

        return 'General Error';
    }

    /**
     * Get a custom message based on the error type.
     *
     * @param \Throwable $exception
     * @return string
     */
    protected function getCustomMessage(Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return 'خطأ في التحقق من المدخلات';
        } elseif ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return 'النموذج المطلوب غير موجود';
        }

        return 'حدث خطأ غير متوقع';
    }
}
