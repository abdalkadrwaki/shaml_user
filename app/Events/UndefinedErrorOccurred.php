<?php

namespace App\Events;

use Exception;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UndefinedErrorOccurred
{
    use Dispatchable, SerializesModels;

    public $exception;

    /**
     * إنشاء نسخة جديدة من الحدث.
     *
     * @param \Exception $exception
     * @return void
     */
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }
}
