<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="CY BE Demo API",
 *     version="1.0.0",
 *     description="API documentation for CY BE Demo application"
 * )
 * @OA\Server(
 *     url="http://localhost:8080",
 *     description="Local Development Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter JWT Bearer token"
 * )
 */
abstract class Controller
{
    //
}
