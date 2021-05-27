<?php

namespace App\Policies;

use App\Policies\SnipePermissionsPolicy;

class PurchasePolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'purchases';
    }
}
