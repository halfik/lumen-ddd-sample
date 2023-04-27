<?php

namespace App\Http\Controllers;

use App\Http\RequireModel;
use Domains\Common\Notification\Mailer;
use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * @package Application\Http\Controllers
 * @OA\Info(title="Sample API", version="1.0")
 *
 * @SWG\Swagger(
 *     basePath="/api",
 *     schemes={"http", "https"},
 *     host=SWAGGER_LUME_CONST_HOST,
 * ),
 *
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      in="header",
 *      name="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 * ),
 */
abstract class Controller extends BaseController
{
    use RequireModel;

    /**
     * @return Mailer
     */
    public function mailer(): Mailer
    {
        return new Mailer();
    }
}
