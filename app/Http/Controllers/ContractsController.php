<?php
namespace App\Http\Controllers;
use App\Helpers\Helper;
use Input;
use Lang;
use App\Models\Location;
use phpDocumentor\Reflection\Types\Array_;
use Redirect;
use App\Models\Setting;
use App\Models\User;
use App\Models\Asset;
use DB;
use Str;
use Validator;
use View;
use Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Image;
use App\Http\Requests\ImageUploadRequest;

/**
 * This controller handles all actions related to Locations for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class ContractsController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Grab all the locations
        $this->authorize('view', Location::class);
        // Show the page
        return view('contracts/index');
    }

}