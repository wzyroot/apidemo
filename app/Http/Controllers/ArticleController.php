<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Article;

class ArticleController extends Controller
{
    public function index()
    {
        return Article::all();
    }
    public function show(Article $article)
    {
        return Article::find($article);
    }
    public function store(Request $request)
    {
        $artcles =  Article::create($request->all());
        return response()->json($artcles,201);
    }
    public function test()
    {
        var_dump(11111);
    }
}
