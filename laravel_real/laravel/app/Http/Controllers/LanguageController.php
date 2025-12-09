<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    /**
     * Change application language
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeLanguage(Request $request)
    {
        $locale = $request->input('locale', 'en');
        
        // Validate locale
        if (!in_array($locale, ['en', 'tr'])) {
            $locale = 'en';
        }
        
        // Store in session
        Session::put('locale', $locale);
        
        // Set application locale
        App::setLocale($locale);
        
        return redirect()->back()->with('success', __('messages.settings_saved'));
    }
    
    /**
     * Get current language
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentLanguage()
    {
        return response()->json([
            'locale' => App::getLocale(),
            'available_locales' => ['en', 'tr']
        ]);
    }
}
