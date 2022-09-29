<?php

namespace App\Http\Controllers;

use App\Models\Asset;

class RequestsController extends Controller
{
    public function index()
    {

        $this->authorize('view', Asset::class);
        return view('requests/index');
    }
}