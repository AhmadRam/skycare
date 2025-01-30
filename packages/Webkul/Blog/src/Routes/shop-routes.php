<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'blog',
    'middleware' => ['web', 'theme', 'locale', 'currency']
], function () {

    Route::get('/', 'Webkul\Blog\Http\Controllers\Shop\BlogController@index')->defaults('_config', [
        'view' => 'blog::shop.velocity.index',
    ])->name('shop.article.index');

    Route::get('/author/{id}', 'Webkul\Blog\Http\Controllers\Shop\BlogController@authorPage')->defaults('_config', [
        'view' => 'blog::shop.author.index',
    ])->name('shop.blog.author.index');

    Route::group(['prefix' => 'tag'], function () {

        Route::get('/{slug}', 'Webkul\Blog\Http\Controllers\Shop\TagController@index')->defaults('_config', [
            'view' => 'blog::shop.tag.index',
        ])->name('shop.blog.tag.index');

    });

    Route::get('category/{slug}', 'Webkul\Blog\Http\Controllers\Shop\CategoryController@index')->defaults('_config', [
        'view' => 'blog::shop.category.index',
    ])->name('shop.blog.category.index');

    Route::get('/{blog_slug}', 'Webkul\Blog\Http\Controllers\Shop\BlogController@view')->defaults('_config', [
        'view' => 'blog::shop.velocity.view',
    ])->name('shop.article.view');


});
    Route::get('/api/v1/blogs', 'Webkul\Blog\Http\Controllers\Shop\API\Blogs\BlogController@list');
    Route::post('/api/v1/blog/comment/store', 'Webkul\Blog\Http\Controllers\Shop\CommentController@store')->name('shop.blog.comment.store');
    Route::get('/api/v1/blog/category', 'Webkul\Blog\Http\Controllers\Shop\API\Blogs\BlogController@category_list')->name('shop.blog.category.list');
    Route::get('/api/v1/blog/tag', 'Webkul\Blog\Http\Controllers\Shop\API\Blogs\BlogController@tag_list')->name('shop.blog.tag.list');
