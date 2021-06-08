<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use App\Policies\CheckoutablePermissionsPolicy;

class SalePolicy extends CheckoutablePermissionsPolicy
{
    protected function columnName()
    {
        return 'sales';
    }

    public function review(User $user, Sale $sale = null)
    {
        return $user->hasAccess('sales.review');
    }
}
