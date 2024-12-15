<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\DataGrids\Settings\InventorySourcesDataGrid;
use Webkul\Admin\DataGrids\Settings\InventoryTransfersDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\InventorySourceRequest;
use Webkul\Inventory\Models\InventoryTransfer;
use Webkul\Inventory\Repositories\InventorySourceRepository;
use Webkul\Product\Helpers\Indexers\Inventory;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;

class InventorySourceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected InventorySourceRepository $inventorySourceRepository,
        protected ProductInventoryRepository $productInventoryRepository,
        protected ProductRepository $productRepository,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(InventorySourcesDataGrid::class)->toJson();
        }

        return view('admin::settings.inventory-sources.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::settings.inventory-sources.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(InventorySourceRequest $inventorySourceRequest)
    {
        Event::dispatch('inventory.inventory_source.create.before');

        $data = request()->only([
            'code',
            'name',
            'description',
            'latitude',
            'longitude',
            'priority',
            'contact_name',
            'contact_email',
            'contact_number',
            'contact_fax',
            'country',
            'state',
            'city',
            'street',
            'postcode',
            'status',
        ]);

        $inventorySource = $this->inventorySourceRepository->create($data);

        Event::dispatch('inventory.inventory_source.create.after', $inventorySource);

        session()->flash('success', trans('admin::app.settings.inventory-sources.create-success'));

        return redirect()->route('admin.settings.inventory_sources.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $inventorySource = $this->inventorySourceRepository->findOrFail($id);

        return view('admin::settings.inventory-sources.edit', compact('inventorySource'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(InventorySourceRequest $inventorySourceRequest, int $id)
    {
        Event::dispatch('inventory.inventory_source.update.before', $id);

        if (! $inventorySourceRequest->status) {
            $inventorySourceRequest['status'] = 0;
        }

        $data = $inventorySourceRequest->only([
            'code',
            'name',
            'description',
            'latitude',
            'longitude',
            'priority',
            'contact_name',
            'contact_email',
            'contact_number',
            'contact_fax',
            'country',
            'state',
            'city',
            'street',
            'postcode',
            'status',
        ]);

        $inventorySource = $this->inventorySourceRepository->update($data, $id);

        Event::dispatch('inventory.inventory_source.update.after', $inventorySource);

        session()->flash('success', trans('admin::app.settings.inventory-sources.update-success'));

        return redirect()->route('admin.settings.inventory_sources.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->inventorySourceRepository->findOrFail($id);

        if ($this->inventorySourceRepository->count() == 1) {
            return response()->json(['message' => trans('admin::app.settings.inventory-sources.last-delete-error')], 400);
        }

        try {
            Event::dispatch('inventory.inventory_source.delete.before', $id);

            $this->inventorySourceRepository->delete($id);

            Event::dispatch('inventory.inventory_source.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.settings.inventory-sources.delete-success'),
            ]);
        } catch (\Exception $e) {
            report($e);
        }

        return new JsonResponse([
            'message' => trans('admin::app.settings.inventory-sources.delete-failed', ['name' => 'admin::app.settings.inventory_sources.index.title']),
        ], 500);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function indexTransfer()
    {
        if (request()->ajax()) {
            return app(InventoryTransfersDataGrid::class)->toJson();
        }

        return view('admin::settings.inventory-sources.index_transfer');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function transfer()
    {
        $inventories = $this->inventorySourceRepository->get();

        return view('admin::settings.inventory-sources.transfer', compact('inventories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function storeTransfer(Request $request)
    {
        $params = $request->all();

        $product = $this->productRepository->find($params['products'][0]);

        $from_qty = $product->inventory_source_qty($params['from']);
        if ($from_qty == 0) {
            session()->flash('error', "No quantity");

            return redirect()->back();
        }

        $to_qty = $product->inventory_source_qty($params['to']);

        $data['inventories'] = [
            $params['from']  => $from_qty - $params['qty'],
            $params['to']  => $to_qty + $params['qty']
        ];

        $this->productInventoryRepository->saveInventories($data, $product);

        app(Inventory::class)->reindexRows([$product]);

        InventoryTransfer::create([
            'from_inventory_id' => $params['from'],
            'to_inventory_id' => $params['to'],
            'quantity' => $params['qty'],
            'product_id' => $params['products'][0],
        ]);

        session()->flash('success', trans('admin::app.settings.inventory-sources.update-success'));

        return redirect()->route('admin.settings.inventory_sources.index_transfer');
    }
}
