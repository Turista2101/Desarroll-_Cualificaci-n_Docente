<?php

namespace App\Http\Controllers\Constantes;
use app\Constants\CodigoCiiu;

use Illuminate\Http\Request;

class ConstantsController
{
      /**
     * Devuelve un array con las constantes definidas.
     *
     * @return JsonResponse
     */

    public function getConstants()
    {
    //     return response()->json([
    //         'codigos_ciiu' => CodigoCiiu::all()
    //  ]);
    }

}
