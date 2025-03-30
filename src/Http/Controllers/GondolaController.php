<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Planogram\Http\Controllers;

use App\Http\Controllers\Controller;
use Callcocam\Planogram\Models\Gondola;
use Illuminate\Http\Request;

class GondolaController extends Controller
{
    public function updateScaleFactor(Gondola $gondola, Request $request)
    {
        $gondola->update(['scale_factor' => $request->scale_factor]);

        return redirect()
            ->back()
            ->with('success', __('Scale factor updated successfully.'));
    }
}
