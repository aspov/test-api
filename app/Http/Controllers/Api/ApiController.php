<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GeoJob;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Laravel OpenApi Demo Documentation",
 *      description="L5 Swagger OpenApi description",
 *
 *      @OA\Contact(
 *          email="admin@admin.com"
 *      ),
 *
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Demo API Server"
 * )

 *
 * @OA\Tag(
 *     name="api",
 *     description="API routes"
 * )
 */
class ApiController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/register",
     *      operationId="register",
     *      tags={"api"},
     *      summary="Register users",
     *      description="Returns tokens",
     *
     *      @OA\Parameter(
     *          name="email",
     *          description="email",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              example="example@mail.com"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="password",
     *          description="password",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *               @OA\Examples(example="result", value={"access_token": "qwe123qwe","refresh_token": "qwe123ddd"}, summary="Success"),
     *               @OA\Examples(example="bool", value={"error":{"password":"The password field confirmation does not match."}}, summary="Error")
     *          )
     *       ),
     *
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', Rules\Password::defaults()],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }
        $user = User::create([
            'name' => '',
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($user->generateTokens());
    }

    /**
     * @OA\Get(
     *      path="/api/login",
     *      operationId="login",
     *      tags={"api"},
     *      summary="Login users",
     *      description="Returns tokens",
     *
     *      @OA\Parameter(
     *          name="email",
     *          description="email",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              example="example@mail.com"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="password",
     *          description="password",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *               @OA\Examples(example="result", value={"access_token": "qwe123qwe","refresh_token": "qwe123ddd"}, summary="Success"),
     *               @OA\Examples(example="bool", value={"error":{"password":"The password field confirmation does not match."}}, summary="Error")
     *          )
     *       ),
     *
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', Rules\Password::defaults()],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }
        $user = User::where(['email' => $request->email])->first();
        abort_if(empty($user) || !Hash::check($request->password, $user->password), Response::HTTP_UNAUTHORIZED, 'Unauthenticated');

        return response()->json($user->generateTokens());
    }

    /**
     * @OA\Get(
     *      path="/api/refresh",
     *      operationId="refresh",
     *      tags={"api"},
     *      summary="Refresh tokens",
     *      description="Returns tokens",
     *
     *      @OA\Parameter(
     *          name="token",
     *          description="refresh token",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              example="3bbO6y8cO0KHSqUxoSdj0A1wNS98CwLM8j5CgBXu"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *               @OA\Examples(example="result", value={"access_token": "qwe123qwe","refresh_token": "qwe123ddd"}, summary="Success"),
     *               @OA\Examples(example="bool", value={"error":{"password":"The password field confirmation does not match."}}, summary="Error")
     *          )
     *       ),
     *
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function refresh(Request $request)
    {
        $token = UserToken::where(['token' => $request->token, 'type' => 'refresh'])->first();
        abort_if(empty($token) || $token->isExpired(), Response::HTTP_UNAUTHORIZED, 'Unauthenticated');

        return response()->json($token->user->generateTokens());
    }

    /**
     * @OA\Get(
     *      path="/api/save-location",
     *      operationId="save-location",
     *      tags={"api"},
     *      summary="Save user location",
     *      description="Returns status of operation",
     *
     *      @OA\Parameter(
     *          name="token",
     *          description="access token",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              example="3bbO6y8cO0KHSqUxoSdj0A1wNS98CwLM8j5CgBXu"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="lon",
     *          description="longitude",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="number",
     *              example="48.403131"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="lat",
     *          description="latitude",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              example="54.314194"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *               @OA\Examples(example="result", value={"status": "success"}, summary="Success"),
     *               @OA\Examples(example="bool", value={"error":"lon or lat not valid"}, summary="Error")
     *          )
     *       ),
     *
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function saveLocation(Request $request)
    {
        $token = UserToken::where(['token' => $request->token, 'type' => 'access'])->first();
        abort_if(empty($token) || $token->isExpired(), Response::HTTP_UNAUTHORIZED, 'Unauthenticated');
        $user = $token->user;

        if (floatval($request->lon) && floatval($request->lat)) {
            GeoJob::dispatch($request->lon, $request->lat, $user);

            return response()->json(['status' => 'success']);
        }

        return response()->json(['error' => 'lon or lat not valid']);
    }

    /**
     * @OA\Get(
     *      path="/api/get-location",
     *      operationId="get-location",
     *      tags={"api"},
     *      summary="Get users locations",
     *      description="Returns the object with 10 location items per page",
     *
     *      @OA\Parameter(
     *          name="token",
     *          description="access token",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              example="3bbO6y8cO0KHSqUxoSdj0A1wNS98CwLM8j5CgBXu"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="start-date",
     *          description="Start date",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type = "string",
     *              format = "date",
     *              example = "2023-10-01"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="end-date",
     *          description="End date",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type = "string",
     *              format = "date",
     *              example = "2023-10-01"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="page",
     *          description="number of page",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="number",
     *              example="1"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *               example={
     *                  "all_pages": 2,
     *                  "current_page": 1,
     *                  "records_count": 13,
     *                  "items": 
     *                      {
     *                          "id": 1,
     *                          "user_id": 1,
     *                          "address": "Россия, Ульяновск, улица Гагарина, 15",
     *                          "lat": "48.391973",
     *                          "lon": "54.324167",
     *                          "created_at": "2023-10-02T04:26:39.000000Z",
     *                          "updated_at": "2023-10-02T04:26:39.000000Z"
     *                      }     
     *               }
     *          )
     *       ),
     *
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getLocation(Request $request)
    {
        $page = $request->get('page', 1);
        $token = UserToken::where(['token' => $request->token, 'type' => 'access'])->first();
        abort_if(empty($token) || $token->isExpired(), Response::HTTP_UNAUTHORIZED, 'Unauthenticated');
        $user = $token->user;
        $locationsQuery = UserLocation::where([
            'user_id' => $user->id,
        ]);
        if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $request->start_date)) {
            $locationsQuery->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($request->start_date)));
        }

        if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $request->end_date)) {
            $locationsQuery->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($request->end_date)));
        }
        $count = $locationsQuery->count();
        $limitPerPage = 10;
        $allPages = ceil(($count / $limitPerPage));
        $page = (int) $request->page;
        if ($allPages > 1 && $page <= $allPages) {
            $locationsQuery->offset($page - 1)->limit($limitPerPage);
        }
        $result = [
            'all_pages' => $allPages,
            'current_page' => $page,
            'records_count' => $count,
            'items' => $locationsQuery->get(),
        ];

        return response()->json($result);
    }
}
