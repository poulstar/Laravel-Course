<?php

namespace App\Http\Controllers;

use App\Actions\AddressAction;
use App\Actions\CategoryAction;
use App\Actions\CommentAction;
use App\Actions\OrderAction;
use App\Actions\ProductAction;
use App\Actions\RCAction;
use App\Actions\SessionAction;
use App\Actions\TagAction;
use App\Actions\TransactionAction;
use App\Actions\UserAction;
use App\Http\Requests\AddCommentRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicController extends Controller
{
    public function index() {
        return redirect()->route('home');
    }
    public function cart() {
        return view('public.cart');
    }

    public function checkout() {
        if (Auth::guest())
            return redirect(route('login'));
        $addresses = AddressAction::getUserAddresses(Auth::id());
        $cities = RCAction::getAllCity();
        return view('public.checkout', compact('addresses', 'cities'));
    }

    public function postCheckout(CheckoutRequest $request){
        if (!empty(SessionAction::getBasket())) {
            if ($request->input('newAddress')){
                $currentAddressID = AddressAction::addAddress($request);
            }else{
                $currentAddressID = $request->input('selectedPreviousAddress');
            }

            list($newOrder_id, $newOrder_pay_price) = OrderAction::addOrder($currentAddressID);

            OrderAction::addNewList($newOrder_id);

            $status = TransactionAction::newTransaction($newOrder_id, $newOrder_pay_price);
            if($status['error'] == 1)
                return redirect(route('adminVisitOrder'));
            else
                return redirect($status['link']);
        }else
            return redirect(route('home'));
    }
    public function callback(Request $request)
    {
        if (!$request->input('order_id'))
            return redirect(route('visitOrder'));
        TransactionAction::responseToCallback($request);
        return redirect(route('visitTransaction'));
    }

    public function sendForPay($order_id)
    {
        $order = OrderAction::getOrder($order_id);
        $order_id = $order->id;
        $order_pay_price = $order->pay_price;
        $status = TransactionAction::newTransaction($order_id, $order_pay_price);
            if($status['error'] == 1)
                return redirect(route('adminVisitOrder'));
            else
                return redirect($status['link']);
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
    public function postLogin(LoginRequest $request) {
        $report = UserAction::login($request);
        switch($report)
        {
            case $report['UserNotFound'] == 1:
                return redirect()->back()->with('danger', 'کاربر مورد نظر وجود ندارد! لطفا ثبت نام کنید');
            case $report['WrongPassword'] == 1:
                return redirect()->back()->with('danger', 'رمز عبور صحیح نیست');
            case $report['UserLogin'] == 1:
                return redirect(route('visitUser'));
        }
    }

    public function logout(){
        UserAction::logout();
        return redirect(route('home'));
    }

    public function postRegister(RegisterRequest $request) {
        $report = UserAction::register($request);
        if ($report['phone'] == 1)
            return redirect()->back()->with('danger','کاربری با این شماره تماس وجود دارد');
        if ($report['email'] == 1)
            return redirect()->back()->with('danger', 'کاربری با این ایمیل وجود دارد');
        return redirect(route('visitUser'));
    }

    public function product() {
        $tag = null;
        $categories = null;
        $products = ProductAction::getAllProducts();
        $newestMenProducts = ProductAction::getProductWithSelectedCategory(1,3);
        $newestWomenProducts = ProductAction::getProductWithSelectedCategory(2,3);
        return view('public.product',compact('tag','categories','products','newestMenProducts','newestWomenProducts'));
    }
    public function filterProductByCategory($category_id) {
        $tag = null;
        $checkCategory = CategoryAction::getCategory($category_id);
        if (!$checkCategory) {
            return redirect(route('product'));
        }else
        {
            $categories = CategoryAction::getAllCategoriesWithNode($category_id);
            $products = ProductAction::getProductWithSelectedCategory($category_id,null);
            $newestMenProducts = ProductAction::getProductWithSelectedCategory(1,3);
            $newestWomenProducts = ProductAction::getProductWithSelectedCategory(2,3);
            return view('public.product',compact('tag','categories','products','newestMenProducts','newestWomenProducts'));
        }
    }
    public function filterProductByTag($tag_id) {
        $tag = TagAction::getTag($tag_id);
        if ($tag)
        {
            $categories = null;
            $products = ProductAction::getAllProductsWithTagID($tag_id);
            $newestMenProducts = ProductAction::getProductWithSelectedCategory(1,3);
            $newestWomenProducts = ProductAction::getProductWithSelectedCategory(2,3);
            return view('public.product',compact('tag','categories','products','newestMenProducts','newestWomenProducts'));
        }else
            return redirect(route('home'));
    }

    public function register() {
        return view('public.register');
    }

    public function singleProduct($product_id) {
        $product = ProductAction::getProduct($product_id);
        $lestProducts = ProductAction::getLastProducts(3);
        return view('public.singleProduct', compact('product','lestProducts'));
    }

    public function tac() {
        return view('public.tac');
    }

    public function postNewComment(AddCommentRequest $request, Product $product){
        CommentAction::addComment($request, $product->id);
        return redirect(route('visitComment'));
    }
}
