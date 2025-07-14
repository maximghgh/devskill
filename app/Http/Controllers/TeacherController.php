<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        // можно передать в view любые данные, например авторизованного юзера
        return view('app');
    }
}

?>