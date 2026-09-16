<?php

namespace App\Models\Traits;

use App\Events\CheckoutableRent;
use App\Events\CheckoutableSell;
use App\Exceptions\CheckoutNotAllowed;
use App\Models\Statuslabel;
use App\Models\User;
use Carbon\Carbon;

trait ChecksOutToDeals
{
    /**
     * Sell the asset out to the target
     *
     * @author [S. Markin] [<markin@legis-s.ru>]
     *
     * @param  User  $user
     * @param  User  $admin
     * @param  Carbon  $checkout_at
     * @param  Carbon  $expected_checkin
     * @param  string  $note
     * @param  null  $name
     */
    public function sell(mixed $target, mixed $admin = null, mixed $checkout_at = null, mixed $note = null, mixed $name = null): bool
    {
        if (! $target) {
            return false;
        }
        if ($this->is($target)) {
            throw new CheckoutNotAllowed('You cannot check an asset out to itself.');
        }

        $this->last_checkout = $checkout_at;
        $this->location_id = null;
        $this->rtd_location_id = null;
        $this->name = $name;

        $status = Statuslabel::where('name', 'Продано')->first();
        $this->status_id = $status->id;
        $this->assignedTo()->associate($target);

        $originalValues = $this->getRawOriginal();

        // attempt to detect change in value if different from today's date
        if ($checkout_at && strpos($checkout_at, date('Y-m-d')) === false) {
            $originalValues['action_date'] = date('Y-m-d H:i:s');
        }
        if ($this->save()) {
            if (is_int($admin)) {
                $checkedOutBy = User::findOrFail($admin);
            } elseif ($admin && get_class($admin) === User::class) {
                $checkedOutBy = $admin;
            } else {
                $checkedOutBy = auth()->user();
            }
            event(new CheckoutableSell($this, $target, $checkedOutBy, $note, $originalValues));
            $this->increment('checkout_counter', 1);

            return true;
        }

        return false;
    }

    /**
     * Rent the asset out to the target
     *
     * @author [S. Markin] [<markin@legis-s.ru>]
     *
     * @param  Carbon  $checkout_at
     * @param  string  $note
     * @param  null  $name
     *
     * @since [v3.0]
     */
    public function rent(mixed $target, mixed $admin = null, mixed $checkout_at = null, mixed $note = null, mixed $name = null): bool
    {
        if (! $target) {
            return false;
        }
        if ($this->is($target)) {
            throw new CheckoutNotAllowed('You cannot check an asset out to itself.');
        }

        $this->last_checkout = $checkout_at;
        $this->location_id = null;
        $this->rtd_location_id = null;
        $this->name = $name;

        $status = Statuslabel::where('name', 'В аренде')->first();
        $this->status_id = $status->id;
        $this->assignedTo()->associate($target);

        $originalValues = $this->getRawOriginal();

        // attempt to detect change in value if different from today's date
        if ($checkout_at && strpos($checkout_at, date('Y-m-d')) === false) {
            $originalValues['action_date'] = date('Y-m-d H:i:s');
        }

        if ($this->save()) {
            if (is_int($admin)) {
                $checkedOutBy = User::findOrFail($admin);
            } elseif ($admin && get_class($admin) === User::class) {
                $checkedOutBy = $admin;
            } else {
                $checkedOutBy = auth()->user();
            }
            event(new CheckoutableRent($this, $target, $checkedOutBy, $note, $originalValues));
            $this->increment('checkout_counter', 1);

            return true;
        }

        return false;
    }
}
