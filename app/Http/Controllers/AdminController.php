<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Показать главную страницу админ-панели.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Здесь возвращаем Blade-шаблон админки, например admin/dashboard.blade.php
        return view('admin.dashboard');
    }
}
