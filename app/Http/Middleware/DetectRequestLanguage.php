<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Language;

class DetectRequestLanguage
{
    /**
     * Handle an incoming request Accept-Language header.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $acceptLang = $request->header('Accept-Language');
        $languageCode = $acceptLang ? substr($acceptLang, 0, 2) : 'en';

        $allowedLocales = collect(File::directories(base_path('lang')))
                ->map(fn ($directory) => basename($directory));
        
        if (!$allowedLocales->contains($languageCode)) {
            Log::error('language not found in app allowed locales for Accept-Language code : ' . $acceptLang);
        }

        App::setLocale($languageCode);

        $request->merge(['language_code' => $languageCode]);

        $response = $next($request);

        $response->headers->set('Content-Language', $languageCode);

        return $response;
    }
}
