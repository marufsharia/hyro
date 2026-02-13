<?php

namespace Marufsharia\Hyro\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PluginController extends Controller
{
    /**
     * Display the plugin manager page.
     */
    public function index()
    {
        return view('hyro::admin.plugins.index');
    }
}
