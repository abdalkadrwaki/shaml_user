<?php

namespace App\Services;

use App\Models\FriendRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FriendRequestValidator
{
    /**
     * التحقق من وجود علاقة صداقة مقبولة وحالة الإيقاف للحوالة.
     *
     * @param int|null $userId إذا لم يتم تمرير المعرف سيتم استخدام auth()->id()
     * @param int $destinationId
     * @param bool $throwException هل يتم رمي استثناء في حال فشل التحقق؟
     * @return FriendRequest|null
     * @throws \Exception
     */
    public static function validate($destinationId, $userId = null, $throwException = true)
    {
        $userId = $userId ?? auth()->id();

        $friendRequest = FriendRequest::where(function ($query) use ($userId, $destinationId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $destinationId);
            })
            ->orWhere(function ($query) use ($userId, $destinationId) {
                $query->where('sender_id', $destinationId)
                      ->where('receiver_id', $userId);
            })
            ->where('status', 'accepted')
            ->lockForUpdate()
            ->first();

        if (!$friendRequest) {
            Log::debug("No valid friendship found for user ID: {$userId} and destination ID: {$destinationId}");
            if ($throwException) {
                throw new \Exception('لم يتم العثور على علاقة صداقة');
            }
            return null;
        }

        // التحقق من حالة الإيقاف للحوالة
        if (
            ($friendRequest->sender_id == $userId && !$friendRequest->stop_syp_2) ||
            ($friendRequest->receiver_id == $userId && !$friendRequest->stop_syp_1)
        ) {
            throw new \Exception('تم إيقاف الحوالة. يرجى مراجعة المكتب.');
        }

        return $friendRequest;
    }
}
