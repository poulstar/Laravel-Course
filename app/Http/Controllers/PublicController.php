<?php

namespace App\Http\Controllers;

use App\Actions\ProductAction;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index() {
        return redirect()->route('home');
    }
    public function cart() {
        return view('public.cart');
    }

    public function checkout() {
        return view('public.checkout');
    }

    public function contact() {
        return view('public.contact');
    }

    public function faq() {
        return view('public.faq');
    }

    public function home() {
        $lestProducts = ProductAction::getLastProducts(4);
        $menProducts = ProductAction::getProductWithSelectedCategory(1,6);
        $womenProducts = ProductAction::getProductWithSelectedCategory(2,6);
        return view('public.home' , compact('lestProducts','menProducts','womenProducts'));
    }

    public function login() {
        return view('public.login');
    }

    public function product() {
        return view('public.product');
    }

    public function register() {
        return view('public.register');
    }

    public function singleProduct() {
        return view('public.singleProduct');
    }

    public function tac() {
        return view('public.tac');
    }
}
