<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

class PublicContentChanged
{
    use Dispatchable;

    public function __construct(public readonly ?Model $model = null) {}
}
