<?php

namespace App\Http\Controllers\Voyager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;

class PagesController extends \TCG\Voyager\Http\Controllers\VoyagerBaseController
{
	use BreadRelationshipParser;

	//***************************************
	//                ______
	//               |  ____|
	//               | |__
	//               |  __|
	//               | |____
	//               |______|
	//
	//  Edit an item of our Data Type BR(E)AD
	//
	//****************************************

	public function edit(Request $request, $id)
	{
		$slug = $this->getSlug($request);

		$dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

		$dataTypeContent = (strlen($dataType->model_name) != 0)
			? app($dataType->model_name)->findOrFail($id)
			: DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

		foreach ($dataType->editRows as $key => $row) {
			$dataType->editRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
		}

		// If a column has a relationship associated with it, we do not want to show that field
		$this->removeRelationshipField($dataType, 'edit');

		// Check permission
		$this->authorize('edit', $dataTypeContent);

		// Check if BREAD is Translatable
		$isModelTranslatable = is_bread_translatable($dataTypeContent);

		$view = 'voyager::bread.edit-add';

		if (view()->exists("voyager::$slug.edit-add")) {
			$view = "voyager::$slug.edit-add";
		}

		return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
	}

	// POST BR(E)AD
	public function update(Request $request, $id)
	{
		$slug = $this->getSlug($request);

		$dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

		// Compatibility with Model binding.
		$id = $id instanceof Model ? $id->{$id->getKeyName()} : $id;

		$data = call_user_func([$dataType->model_name, 'findOrFail'], $id);

		// Check permission
		$this->authorize('edit', $data);

		// Validate fields with ajax
		$val = $this->validateBread($request->all(), $dataType->editRows, $dataType->name, $id);

		if ($val->fails()) {
			return response()->json(['errors' => $val->messages()]);
		}

		if (!$request->ajax()) {
			$this->insertUpdateData($request, $slug, $dataType->editRows, $data);

			event(new BreadDataUpdated($dataType, $data));

			return redirect()
				->route("voyager.{$dataType->slug}.index")
				->with([
					'message'    => __('voyager::generic.successfully_updated')." {$dataType->display_name_singular}",
					'alert-type' => 'success',
				]);
		}
	}

	//***************************************
	//
	//                   /\
	//                  /  \
	//                 / /\ \
	//                / ____ \
	//               /_/    \_\
	//
	//
	// Add a new item of our Data Type BRE(A)D
	//
	//****************************************

	public function create(Request $request)
	{
		$slug = $this->getSlug($request);

		$dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

		// Check permission
		$this->authorize('add', app($dataType->model_name));

		$dataTypeContent = (strlen($dataType->model_name) != 0)
			? new $dataType->model_name()
			: false;

		foreach ($dataType->addRows as $key => $row) {
			$dataType->addRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
		}

		// If a column has a relationship associated with it, we do not want to show that field
		$this->removeRelationshipField($dataType, 'add');

		// Check if BREAD is Translatable
		$isModelTranslatable = is_bread_translatable($dataTypeContent);

		$view = 'voyager::bread.edit-add';

		if (view()->exists("voyager::$slug.edit-add")) {
			$view = "voyager::$slug.edit-add";
		}

		return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
	}

	/**
	 * POST BRE(A)D - Store data.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store(Request $request)
	{
		$slug = $this->getSlug($request);

		$dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

		// Check permission
		$this->authorize('add', app($dataType->model_name));

		// Validate fields with ajax
		$val = $this->validateBread($request->all(), $dataType->addRows);

		if ($val->fails()) {
			return response()->json(['errors' => $val->messages()]);
		}

		if (!$request->has('_validate')) {
			$data = $request->input();
			$data->slug = Str::slug($data->title);

			$data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());
			event(new BreadDataAdded($dataType, $data));

			if ($request->ajax()) {
				return response()->json(['success' => true, 'data' => $data]);
			}

			return redirect()
				->route("voyager.{$dataType->slug}.index")
				->with([
					'message'    => __('voyager::generic.successfully_added_new')." {$dataType->display_name_singular}",
					'alert-type' => 'success',
				]);
		}
	}

	//***************************************
	//                _____
	//               |  __ \
	//               | |  | |
	//               | |  | |
	//               | |__| |
	//               |_____/
	//
	//         Delete an item BREA(D)
	//
	//****************************************

	public function destroy(Request $request, $id)
	{
		$slug = $this->getSlug($request);

		$dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

		// Check permission
		$this->authorize('delete', app($dataType->model_name));

		// Init array of IDs
		$ids = [];
		if (empty($id)) {
			// Bulk delete, get IDs from POST
			$ids = explode(',', $request->ids);
		} else {
			// Single item delete, get ID from URL
			$ids[] = $id;
		}
		foreach ($ids as $id) {
			$data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
			$this->cleanup($dataType, $data);
		}

		$displayName = count($ids) > 1 ? $dataType->display_name_plural : $dataType->display_name_singular;

		$res = $data->destroy($ids);
		$data = $res
			? [
				'message'    => __('voyager::generic.successfully_deleted')." {$displayName}",
				'alert-type' => 'success',
			]
			: [
				'message'    => __('voyager::generic.error_deleting')." {$displayName}",
				'alert-type' => 'error',
			];

		if ($res) {
			event(new BreadDataDeleted($dataType, $data));
		}

		return redirect()->route("voyager.{$dataType->slug}.index")->with($data);
	}

	/**
	 * Remove translations, images and files related to a BREAD item.
	 *
	 * @param \Illuminate\Database\Eloquent\Model $dataType
	 * @param \Illuminate\Database\Eloquent\Model $data
	 *
	 * @return void
	 */
	protected function cleanup($dataType, $data)
	{
		// Delete Translations, if present
		if (is_bread_translatable($data)) {
			$data->deleteAttributeTranslations($data->getTranslatableAttributes());
		}

		// Delete Images
		$this->deleteBreadImages($data, $dataType->deleteRows->where('type', 'image'));

		// Delete Files
		foreach ($dataType->deleteRows->where('type', 'file') as $row) {
			if (isset($data->{$row->field})) {
				foreach (json_decode($data->{$row->field}) as $file) {
					$this->deleteFileIfExists($file->download_link);
				}
			}
		}
	}

	/**
	 * Delete all images related to a BREAD item.
	 *
	 * @param \Illuminate\Database\Eloquent\Model $data
	 * @param \Illuminate\Database\Eloquent\Model $rows
	 *
	 * @return void
	 */
	public function deleteBreadImages($data, $rows, $single_image = NULL)
	{
		foreach ($rows as $row) {
			if ($data->{$row->field} != config('voyager.user.default_avatar')) {
				$this->deleteFileIfExists($data->{$row->field});
			}

			if (isset($row->details->thumbnails)) {
				foreach ($row->details->thumbnails as $thumbnail) {
					$ext = explode('.', $data->{$row->field});
					$extension = '.'.$ext[count($ext) - 1];

					$path = str_replace($extension, '', $data->{$row->field});

					$thumb_name = $thumbnail->name;

					$this->deleteFileIfExists($path.'-'.$thumb_name.$extension);
				}
			}
		}

		if ($rows->count() > 0) {
			event(new BreadImagesDeleted($data, $rows));
		}
	}

	/**
	 * Order BREAD items.
	 *
	 * @param string $table
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function order(Request $request)
	{
		$slug = $this->getSlug($request);

		$dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

		// Check permission
		$this->authorize('edit', app($dataType->model_name));

		if (!isset($dataType->order_column) || !isset($dataType->order_display_column)) {
			return redirect()
				->route("voyager.{$dataType->slug}.index")
				->with([
					'message'    => __('voyager::bread.ordering_not_set'),
					'alert-type' => 'error',
				]);
		}

		$model = app($dataType->model_name);
		$results = $model->orderBy($dataType->order_column, $dataType->order_direction)->get();

		$display_column = $dataType->order_display_column;

		$dataRow = Voyager::model('DataRow')->whereDataTypeId($dataType->id)->whereField($display_column)->first();

		$view = 'voyager::bread.order';

		if (view()->exists("voyager::$slug.order")) {
			$view = "voyager::$slug.order";
		}

		return Voyager::view($view, compact(
			'dataType',
			'display_column',
			'dataRow',
			'results'
		));
	}

	public function update_order(Request $request)
	{
		$slug = $this->getSlug($request);

		$dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

		// Check permission
		$this->authorize('edit', app($dataType->model_name));

		$model = app($dataType->model_name);

		$order = json_decode($request->input('order'));
		$column = $dataType->order_column;
		foreach ($order as $key => $item) {
			$i = $model->findOrFail($item->id);
			$i->$column = ($key + 1);
			$i->save();
		}
	}
}