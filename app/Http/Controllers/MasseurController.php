<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MasseurPost;
use Auth;

class MasseurController extends Controller
{
    public function __construct()
    {
   	   $this->middleware('auth');
   	   $this->middleware('masseur');
    }
    public function index(){
    	return view('masseur.index');
    }
}
