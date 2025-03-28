<?php

namespace App\Services;

use App\Models\FriendRequest;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class FriendService
{
    /**
     * التحقق من وجود علاقة صداقة مقبولة بين مستخدمين
     *
     * @param int $userId
     * @param int $targetUserId
     * @return FriendRequest
     * @throws Exception
     */
    public static function checkAcceptedFriendship(int $userId, int $targetUserId): FriendRequest
    {
        $friendRequest = FriendRequest::where(function ($query) use ($userId, $targetUserId) {
                $query->where('sender_id', $userId)
                    ->where('receiver_id', $targetUserId);
            })
            ->orWhere(function ($query) use ($userId, $targetUserId) {
                $query->where('receiver_id', $userId)
                    ->where('sender_id', $targetUserId);
            })
            ->where('status', 'accepted')
            ->first();

        if (!$friendRequest) {
            throw new Exception('لا يمكن إتمام العملية. يجب أن تكون هناك علاقة صداقة مقبولة بينك وبين المستلم.');
        }

        return $friendRequest;
    }


    public static function loadDestinations(): array
    {
        $currentUserId = Auth::id();

        $friendRequests = FriendRequest::where(function ($query) use ($currentUserId) {
                $query->where('receiver_id', $currentUserId)
                      ->orWhere('sender_id', $currentUserId);
            })
            ->where('status', 'accepted')
            ->get();

        $destinations = [];

        foreach ($friendRequests as $request) {
            if ($request->sender_id == $currentUserId) {
                $partnerId = $request->receiver_id;
                $balance = $request->balance_in_usd_1;
            } else {
                $partnerId = $request->sender_id;
                $balance = $request->balance_in_usd_2;
            }

            $user = User::find($partnerId, ['id', 'Office_name', 'state_user', 'country_user']);

            if ($user) {
                $destinations[] = [
                    'id'          => $user->id,
                    'Office_name'        => $user->Office_name,
                    'state_user'  => $user->state_user,
                    'country_user'=> $user->country_user,
                    'balance'     => $balance,
                ];
            }
        }

        // إزالة التكرارات إن وجدت
        $destinations = collect($destinations)->unique('id')->values()->all();

        return $destinations;
    }

}
