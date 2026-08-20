<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KnowledgeBaseController;
use App\Http\Controllers\Api\KnowledgeDocumentController;

Route::prefix('auth')->group(function () {

    Route::post(
        '/register',
        [AuthController::class, 'register']
    );

    Route::post(
        '/login',
        [AuthController::class, 'login']
    );

    Route::middleware('auth:sanctum')->group(function () {

        Route::get(
            '/me',
            [AuthController::class, 'me']
        );

        Route::post(
            '/logout',
            [AuthController::class, 'logout']
        );
    });

    //////////////////////


        Route::middleware('auth:sanctum')->group(function () {

                Route::apiResource(
                    'knowledge-bases',
                    KnowledgeBaseController::class
                )->except([
                    'create',
                    'edit',
                ]);


                Route::get(
                    'knowledge-bases/{knowledge_base}/documents',
                    [
                        KnowledgeDocumentController::class,
                        'index',
                    ]
                );

                Route::post(
                    'knowledge-bases/{knowledge_base}/documents',
                    [
                        KnowledgeDocumentController::class,
                        'store',
                    ]
                );

                Route::get(
                    'knowledge-bases/{knowledge_base}/documents/{document}',
                    [
                        KnowledgeDocumentController::class,
                        'show',
                    ]
                );

                Route::delete(
                    'knowledge-bases/{knowledge_base}/documents/{document}',
                    [
                        KnowledgeDocumentController::class,
                        'destroy',
                    ]
                );
        });

        ////////////////////////////


    Route::middleware([
    'auth:sanctum',
    'role:admin',
        ])->group(function () {

            Route::get('/admin/test', function () {
                return response()->json([
                    'message' => 'Admin access granted.',
                ]);
            });
        });

        Route::middleware([
            'auth:sanctum',
            'role:admin,agent',
        ])->group(function () {

            Route::get('/support/test', function () {
                return response()->json([
                    'message' => 'Support access granted.',
                ]);
            });
        });


});