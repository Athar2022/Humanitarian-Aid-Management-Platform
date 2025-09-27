<?php

use Illuminate\Support\Facades\Route;

// Route للصفحة الرئيسية
Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to Humanitarian Aid Platform API',
        'version' => '1.0.0',
        'endpoints' => [
            'api_docs' => url('/api'),
            'frontend' => env('FRONTEND_URL', 'http://localhost:5173')
        ]
    ]);
});

// صفحات المصادقة
Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/login', function () {
    return response()->json([
        'message' => 'Login page not implemented yet. Please use API endpoint.',
        'api_endpoint' => 'POST /api/login'
    ]);
});

// Exclude API routes from SPA catch-all
Route::get('/{any}', function () {
    return response()->json([
        'message' => 'This is an API-only application. Please use API endpoints.',
        'available_endpoints' => [
            'register' => 'POST /api/register',
            'login' => 'POST /api/login',
            'docs' => 'GET /docs'
        ],
        'note' => 'Use Postman or similar tools to interact with the API'
    ], 404);
})->where('any', '^(?!api).*$');

// Route للصفحة الرئيسية لـ API
Route::get('/api', function () {
    return redirect('/docs');
});

// // require base_path('routes/api.php');
// Route::prefix('api')->group(function () {
//     require base_path('routes/api.php');
// });

