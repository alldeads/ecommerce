<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Display shopping cart page.
     */
    public function index(): Response
    {
        return Inertia::render('Cart');
    }
}
