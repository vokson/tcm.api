<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\ActionController;

class ApiUser extends Model
{
    protected $fillable = [
        'name', 'surname', 'email', 'password', 'updated_at'
    ];

    protected $hidden = [
        'password', 'access_token',
    ];

    public $timestamps = true;
    protected $dateFormat = 'U';

    public function mayDo(string $nameOfAction)
    {
        return ActionController::take($this->role, $nameOfAction);
    }

}
