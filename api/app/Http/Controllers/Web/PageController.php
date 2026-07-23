<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class PageController extends Controller
{
    public function info(string $slug)
    {
        $view = 'pages.' . $slug;
        if (! View::exists($view)) {
            abort(404);
        }
        return view($view);
    }

    public function company()    { return view('pages.company'); }
    public function contacts()   { return view('pages.contacts'); }
    public function stores()     { return view('pages.stores'); }
    public function installment(){ return view('pages.installment'); }
    public function tradeIn()    { return view('pages.trade-in'); }
    public function offer()      { return view('pages.offer'); }
    public function privacy()    { return view('pages.privacy'); }
    public function review()     { return view('pages.review'); }
    public function blog()       { return view('pages.blog'); }
    public function blogArticle(string $slug) { return view('pages.blog-article', compact('slug')); }
    public function sales()      { return view('pages.sales'); }
    public function salesArticle(string $slug) { return view('pages.sales-article', compact('slug')); }
}
