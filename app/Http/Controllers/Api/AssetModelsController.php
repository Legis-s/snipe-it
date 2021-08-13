<?php
namespace App\Http\Controllers\Api;

use App\Models\Actionlog;
use App\Models\AssetModel;
use App\Models\Asset;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Statuslabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Transformers\AssetModelsTransformer;
use App\Http\Transformers\AssetsTransformer;
use App\Http\Transformers\SelectlistTransformer;
use Illuminate\Support\Facades\Auth;


/**
 * This class controls all actions related to asset models for
 * the Snipe-IT Asset Management application.
 *
 * @version    v4.0
 * @author [A. Gianotto] [<snipe@snipe.net>]
 */
class AssetModelsController extends Controller
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
        $this->authorize('view', AssetModel::class);
        $allowed_columns = ['id','image','name','model_number','eol','notes','created_at','manufacturer','assets_count'];

        $assetmodels = AssetModel::select([
            'models.id',
            'models.image',
            'models.name',
            'model_number',
            'eol',
            'models.notes',
            'models.created_at',
            'category_id',
            'manufacturer_id',
            'depreciation_id',
            'fieldset_id',
            'lifetime',
            'models.deleted_at',
            'models.updated_at',
         ])
            ->with('category','depreciation', 'manufacturer','fieldset')
            ->withCount('assets as assets_count');



        if ($request->filled('status')) {
            $assetmodels->onlyTrashed();
        }

        if ($request->filled('search')) {
            $assetmodels->TextSearch($request->input('search'));
        }

        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($assetmodels) && ($request->get('offset') > $assetmodels->count())) ? $assetmodels->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'models.created_at';

        switch ($sort) {
            case 'manufacturer':
                $assetmodels->OrderManufacturer($order);
                break;
            default:
                $assetmodels->orderBy($sort, $order);
                break;
        }



        $total = $assetmodels->count();
        $assetmodels = $assetmodels->skip($offset)->take($limit)->get();
        return (new AssetModelsTransformer)->transformAssetModels($assetmodels, $total);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', AssetModel::class);
        $assetmodel = new AssetModel;
        $assetmodel->fill($request->all());

        if ($assetmodel->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $assetmodel, trans('admin/models/message.create.success')));
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, $assetmodel->getErrors()));

    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view', AssetModel::class);
        $assetmodel = AssetModel::withCount('assets as assets_count')->findOrFail($id);
        return (new AssetModelsTransformer)->transformAssetModel($assetmodel);
    }

    /**
     * Display the specified resource's assets
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assets($id)
    {
        $this->authorize('view', AssetModel::class);
        $assets = Asset::where('model_id','=',$id)->get();
        return (new AssetsTransformer)->transformAssets($assets, $assets->count());
    }


    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('update', AssetModel::class);
        $assetmodel = AssetModel::findOrFail($id);
        $assetmodel->fill($request->all());
        $assetmodel->fieldset_id = $request->get("custom_fieldset_id");

        if ($assetmodel->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $assetmodel, trans('admin/models/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $assetmodel->getErrors()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', AssetModel::class);
        $assetmodel = AssetModel::findOrFail($id);
        $this->authorize('delete', $assetmodel);

        if ($assetmodel->assets()->count() > 0) {
            return response()->json(Helper::formatStandardApiResponse('error', null,  trans('admin/models/message.assoc_users')));
        }

        if ($assetmodel->image) {
            try  {
                unlink(public_path().'/uploads/models/'.$assetmodel->image);
            } catch (\Exception $e) {
                \Log::info($e);
            }
        }

        $assetmodel->delete();
        return response()->json(Helper::formatStandardApiResponse('success', null,  trans('admin/models/message.delete.success')));

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

        $assetmodels = AssetModel::select([
            'models.id',
            'models.name',
            'models.image',
            'models.model_number',
            'models.manufacturer_id',
            'models.category_id',
        ])->with('manufacturer','category');

        $settings = \App\Models\Setting::getSettings();

        if ($request->filled('search')) {
            $assetmodels = $assetmodels->SearchByManufacturerOrCat($request->input('search'));
        }

        $assetmodels = $assetmodels->OrderCategory('ASC')->OrderManufacturer('ASC')->orderby('models.name', 'asc')->orderby('models.model_number', 'asc')->paginate(50);

        foreach ($assetmodels as $assetmodel) {

            $assetmodel->use_text = '';

            if ($settings->modellistCheckedValue('category')) {
                $assetmodel->use_text .= (($assetmodel->category) ? e($assetmodel->category->name).' - ' : '');
            }

            if ($settings->modellistCheckedValue('manufacturer')) {
                $assetmodel->use_text .= (($assetmodel->manufacturer) ? e($assetmodel->manufacturer->name).' ' : '');
            }

            $assetmodel->use_text .=  e($assetmodel->name);

            if (($settings->modellistCheckedValue('model_number')) && ($assetmodel->model_number!='')) {
                $assetmodel->use_text .=  ' (#'.e($assetmodel->model_number).')';
            }

            $assetmodel->use_image = ($settings->modellistCheckedValue('image') && ($assetmodel->image)) ? url('/').'/uploads/models/'.$assetmodel->image : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($assetmodels);
    }


    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function convert($id)
    {
        $this->authorize('view', AssetModel::class);
//        $assetmodel = AssetModel::withCount('assets as assets_count')->findOrFail($id);

        $assets = Asset::where("model_id",$id)->get();
        $count =count($assets);
        $asset = $assets[0];
        $purchase_cost = null;

        $status_ok = Statuslabel::where('name', 'Доступные')->first();
        $free_count = 0;
        $busy_count = 0;
        $free_id=[];
        $busy_id=[];
        foreach ($assets as &$asset) {
            if ((empty($asset->assigned_to)) && (empty($asset->deleted_at)) &&
                (($asset->assetstatus) && ($asset->assetstatus->deployable == 1))) {
                $free_count++;
                array_push($free_id, $asset->id.",". $asset->asset_tag);
            }

            if (empty($asset->deleted_at) && isset($asset->assigned_to) && $asset->assetstatus->deployable == 1 ){
                $busy_count++;
                array_push($busy_id, $asset->id.",". $asset->asset_tag);
            }
        }

        $all_count = $free_count+$busy_count;

        $category_old = Category::findOrFail($asset->model->category_id);
        $category_new = Category::where("name",$category_old->name)->where("category_type","consumable")->first();;
        if (!$category_new) {
            $category_new = $category_old->replicate();
            $category_new->category_type = "consumable";
            $category_new->save();
        }
        $consumable = new Consumable();
        $consumable->name = $asset->model->name;
        $consumable->qty =$all_count;
        $consumable->category_id= $category_new->id;
        $consumable->manufacturer_id= $asset->manufacturer_id;
        $consumable->purchase_cost = $purchase_cost;

        $consumable->save();
        $comment = "Свободные  [".implode(";",$free_id)."] Выданные [".implode(";",$busy_id)."]";
        $consumable->locations()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'user_id' => Auth::id(),
            'quantity' => $all_count,
            'comment' => $comment,
            'cost' => $consumable->purchase_cost,
            'type' => ConsumableAssignment::CONVERTED,
//            'assigned_to' => $assigned_to->id,
//            'assigned_type' => $assigned_type,
        ]);

        foreach ($assets as &$asset) {

            if ((empty($asset->assigned_to)) && (empty($asset->deleted_at)) &&
                (($asset->assetstatus) && ($asset->assetstatus->deployable == 1))) {
                $log = new Actionlog();
                $log->user_id = Auth::id();
                $log->action_type = 'converted';
                $log->target_type = "App\Models\Consumable";
                $log->target_id = $consumable->id;
                $log->item_id = $asset->id;
                $log->item_type = "App\Models\Asset";
//                $log->note = json_encode($request->all());
                $log->save();
            }

            if (empty($asset->deleted_at) && isset($asset->assigned_to) && $asset->assetstatus->deployable == 1 ){
                $consumable->locations()->attach($consumable->id, [
                    'consumable_id' => $consumable->id,
                    'user_id' => Auth::id(),
                    'quantity' => 1,
//            'comment' => $comment,
                    'cost' => $consumable->purchase_cost,
                    'type' => ConsumableAssignment::ISSUED,
                    'assigned_to' => $asset->assigned_to,
                    'assigned_type' => $asset->assigned_type,
                ]);
                $log = new Actionlog();
                $log->user_id = Auth::id();
                $log->action_type = 'converted';
                $log->target_type = "App\Models\Consumable";
                $log->target_id = $consumable->id;
                $log->item_id = $asset->id;
                $log->item_type = "App\Models\Asset";
//                $log->note = json_encode($request->all());
                $log->save();
            }
        }

        foreach ($assets as &$asset) {
            \Debugbar::info($asset->assetstatus);
            if ((empty($asset->assigned_to)) && (empty($asset->deleted_at)) &&
                (($asset->assetstatus) && ($asset->assetstatus->deployable == 1))) {
                $asset->delete();
            }

            if (empty($asset->deleted_at) && isset($asset->assigned_to) && $asset->assetstatus->deployable == 1 ){
                $asset->delete();
            }
        }


        return "No credentals";
    }

}
