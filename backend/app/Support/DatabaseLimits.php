<?php

namespace App\Support;

class DatabaseLimits
{
    // UNSIGNED INT and DECIMAL(14, 2) used in the migrations.
    public const MAX_STOCK = 4294967295;

    public const MAX_AMOUNT = '999999999999.99';
}
