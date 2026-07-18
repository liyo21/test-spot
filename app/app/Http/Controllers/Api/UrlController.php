<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\UrlNotFoundException;
use App\Models\Url;
use App\Services\UrlShortener;
use App\Utils\ResponseUtils;
use App\Http\Requests\UrlRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
* @OA\Info(title="API Test Spot2", version="1.0")
*
*/

class UrlController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/urls",
     *     summary="Obtener la lista de URLs acortadas",
    *     @OA\Response(
    *         response=200,
    *         description="Lista de URLs acortadas obtenida exitosamente.",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(
    *                 property="status",
    *                 type="string",
    *                 example="OK"
    *             ),
    *             @OA\Property(
    *                 property="message",
    *                 type="string",
    *                 example="Url lists"
    *             ),
    *             @OA\Property(
    *                 property="data",
    *                 type="array",
    *                 @OA\Items(
    *                     type="object",
    *                     @OA\Property(
    *                         property="original_url",
    *                         type="string",
    *                         example="https://google.com"
    *                     ),
    *                     @OA\Property(
    *                         property="shortened_url",
    *                         type="string",
    *                         example="http://localhost:8000/abc123"
    *                     )
    *                 )
    *             )
    *         )
    *     )
     * )
     * @OA\Post(
     *     path="/api/urls",
     *     summary="Crear URL Shortener",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"url"},
     *                 @OA\Property(
     *                     property="url",
     *                     type="string",
     *                     example="https://google.com"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="URL acortada generada exitosamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="url_decode",
     *                 type="string",
     *                 example="https://google.com"
     *             ),
     *             @OA\Property(
     *                 property="url_encode",
     *                 type="string",
     *                 example="v4c9r9DT"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación de entrada.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="url",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string",
     *                     example="Por favor, ingresa una URL válida."
     *                 )
     *             )
     *         )
     *     ),
     * )
     * @OA\Get(
     *     path="/api/urls/{shortenUrl}",
     *     summary="Mostrar URL original para una URL acortada",
     *     @OA\Parameter(
     *         name="shortenUrl",
     *         in="path",
     *         required=true,
     *         description="El identificador de la URL acortada",
     *         @OA\Schema(
     *             type="string",
     *             example="KVDtivYE"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="URL original encontrada exitosamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="OK"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="La shortenUrl KVDtivYE fué encontrada"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="original_url",
     *                     type="string",
     *                     example="https://google.com"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="La URL acortada no fue encontrada.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="NOK"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="La shortenUrl KVDtivYE no fué encontrada"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     * )
     * @OA\Delete(
     *     path="/api/shortenUrl/{shortenUrl}",
     *     summary="Eliminar una URL acortada",
     *     @OA\Parameter(
     *         name="shortenUrl",
     *         in="path",
     *         required=true,
     *         description="El identificador de la URL acortada",
     *         @OA\Schema(
     *             type="string",
     *             example="KVDtivYE"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="URL acortada eliminada exitosamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="OK"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The shortenUrl KVDtivYE deleted"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="La URL acortada no fue encontrada.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="NOK"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The shortenUrl KVDtivYE was not found"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     * )
     */

    public function __construct(private readonly UrlShortener $urlShortener)
    {
    }

    public function index() : JsonResponse
    {
        $getUrls = Url::all()
                        ->makeHidden(['id','created_at', 'updated_at']);

        $response = ResponseUtils::makeResponse('OK', 'Url lists', $getUrls);


        return response()->json($response, 200);
    }

    public function create(UrlRequest $request) : JsonResponse
    {

        $url = $this->urlShortener->shorten($request->validated('url'));

        $data = [
            'original_url'  => $url->original_url,
            'shortened_url' => $url->shortened_url,
        ];

        $response = ResponseUtils::makeResponse('OK', 'Url Shortened stored correctly', $data);

        return response()->json($response, 200);
    }

    public function show($shortenUrl) : JsonResponse
    {

        $findUrl = Url::where('shortened_url', $shortenUrl)->first();

        if (!$findUrl) {
            throw new UrlNotFoundException();
        }

        $data = [
            'original_url'  => $findUrl->original_url,
        ];
        $response = ResponseUtils::makeResponse('OK', 'The shortenUrl '.$shortenUrl.' was found', $data);

        return response()->json($response, 200);
    }

    public function destroy($shortenUrl) : JsonResponse
    {

        $url    = Url::where('shortened_url', $shortenUrl)->first();

        if (!$url) {
            throw new UrlNotFoundException();
        }

        $url->delete();
        $response = ResponseUtils::makeResponse('OK', 'The shortenUrl '.$shortenUrl.' deleted', []);

        return response()->json($response, 200);

    }
}
