<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Transformers\InvoiceTypesTransformer;
use App\Http\Transformers\LocationsTransformer;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InvoiceType;
use App\Http\Transformers\SelectlistTransformer;

class InvoiceTypesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view', Location::class);
        $allowed_columns = ['id', 'name', 'created_at', 'bitrix_id', 'active'];

        $invoicetypes = InvoiceType::select([
            'invoice_types.id',
            'invoice_types.name',
            'invoice_types.created_at',
            'invoice_types.bitrix_id',
            'invoice_types.active',
        ]);

        if ($request->filled('search')) {
            $invoicetypes = $invoicetypes->TextSearch($request->input('search'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $invoicetypes->count()) ? $invoicetypes->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';


        $total = $invoicetypes->count();
        $invoicetypes = $invoicetypes->skip($offset)->take($limit)->get();

        return (new InvoiceTypesTransformer)->transformInvoiceTypes($invoicetypes, $total);
    }

    /**
     * Gets a paginated collection for the select2 menus
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0.16]
     * @see \App\Http\Transformers\SelectlistTransformer
     *
     */
    public function selectlist(Request $request)
    {

        $invoice_types = InvoiceType::select([
            'id',
            'name',
            'active'
        ])->where('active', true);

        if ($request->filled('search')) {
            $invoice_types = $invoice_types->where('name', 'LIKE', '%'.$request->get('search').'%');
        }

        $invoice_types = $invoice_types->orderBy('name', 'ASC')->paginate(50);

        // Loop through and set some custom properties for the transformer to use.
        // This lets us have more flexibility in special cases like assets, where
        // they may not have a ->name value but we want to display something anyway
        foreach ($invoice_types as $invoice_type) {
            $invoice_type->use_text = $invoice_type->name;
        }

        return (new SelectlistTransformer)->transformSelectlist($invoice_types);

    }
}
