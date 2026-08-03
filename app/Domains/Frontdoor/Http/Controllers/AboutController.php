<?php

namespace App\Domains\Frontdoor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        $teams = [
            [
                'name' => 'John Doe',
                'role' => 'Fotografer',
                'image' => 'assets/images/team-1.png',
            ],
            [
                'name' => 'John Doe',
                'role' => 'Fotografer',
                'image' => 'assets/images/team-2.png',
            ],
            [
                'name' => 'John Doe',
                'role' => 'Fotografer',
                'image' => 'assets/images/team-3.png',
            ],
            [
                'name' => 'John Doe',
                'role' => 'Fotografer',
                'image' => 'assets/images/team-4.png',
            ],
            [
                'name' => 'John Doe',
                'role' => 'Fotografer',
                'image' => 'assets/images/team-5.png',
            ],
        ];

        return view('frontdoor.about.index', compact('teams'));
    }
}
