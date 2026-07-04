<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriesController extends Controller
{
	public function index() {
		return Inertia::render('Categories/Index', [
			'categories' => Category::all()
		]);
	}

	public function edit(Category $category) {
		return Inertia::render('Categories/Edit', [
			'category' => $category
		]);
	}
}
