<?php

namespace App\Providers;

use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('success', function (mixed $data = null, string $message = 'Success', int $status = 200) {
            return ApiResponse::success($data, $message, $status);
        });

        Response::macro('error', function (string $message = 'Error', int $status = 400, mixed $errors = null) {
            return ApiResponse::error($message, $status, $errors);
        });

        Response::macro('created', function (mixed $data = null, string $message = 'Created successfully') {
            return ApiResponse::created($data, $message);
        });

        Response::macro('noContent', function (string $message = 'Deleted successfully') {
            return ApiResponse::noContent($message);
        });
    }
}
