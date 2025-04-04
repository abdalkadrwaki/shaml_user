<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo'         => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'user_address'  => ['required', 'string', 'max:255'],
            'country_user'  => ['required', 'string', 'max:255'],
            'state_user'    => ['required', 'string', 'max:255'],
           'link_number' => ['required', 'digits:16'],
            'Office_name'   => ['required', 'string', 'max:255'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name'          => $input['name'],
                'email'         => $input['email'],
                'user_address'  => $input['user_address'],
                'country_user'  => $input['country_user'],
                'state_user'    => $input['state_user'],
                'link_number'   => $input['link_number'],
                'Office_name'   => $input['Office_name'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name'             => $input['name'],
            'email'            => $input['email'],
            'user_address'     => $input['user_address'],
            'country_user'     => $input['country_user'],
            'state_user'       => $input['state_user'],
            'link_number'      => $input['link_number'],
            'Office_name'      => $input['Office_name'],
            'email_verified_at'=> null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
