<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Validation\Rule;

use App\Models\Device;
use App\Http\Requests\DeviceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\Log;

/**
 * Class DeviceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DeviceCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;


    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Device::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/device');
        CRUD::setEntityNameStrings(__('messages.device'), __('messages.devices'));
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::addButtonFromModelFunction('top', 'save project', 'save_project');
        CRUD::addButtonFromModelFunction('top', 'load project', 'load_project');
        CRUD::addButton('top', 'auto_create_appliances', 'view', 'vendor.backpack.crud.buttons.auto_create_appliances_button');
        CRUD::addButtonFromModelFunction('top', 'Device', 'scan_devices');

        // Add a toggle filter to hide/show devices with associated appliances and channels
        CRUD::addFilter([
            'type'  => 'simple',
            'name'  => 'hide_with_appliances',
            'label' => __('messages.hide_devices_with_appliances')
        ], false, function ($value) {
            // When the toggle is on, hide devices with appliances
            if ($value == true) {
                CRUD::addClause('doesntHave', 'appliances');
            }
        });


        CRUD::setFromDb(); // set columns from db columns.
        CRUD::removeColumn('is_protected');
        CRUD::column('device_type')->type('select2')->entity('deviceType')->attribute("device_type_name")->model('App\Models\DeviceType');
        CRUD::column('created_at')->type('datetime');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(DeviceRequest::class);
        CRUD::setFromDb(); // set fields from db columns.
        CRUD::addField([
            'name' => 'device_type',
            'label' => __('messages.device_type'),
            'type' => 'select2',
            'entity' => 'deviceType',
            'attribute' => "device_type_name",
            'model' => 'App\Models\DeviceType',
        ]);

        // validation rules
        $rules = [
            'device_type' => 'required',
            'device_name' => [
                'required',
                'min:1',
                'unique:devices,device_name',
            ],
            'device_address' => [
                'required',
                'min:3',
                'unique:devices,device_address',
            ],

        ];
        $messages = [
            'device_type.required' => __('messages.field_required'),
            'device_name.required' => __('messages.field_required'),
            'device_name.min' => __('messages.min_length', ['min' => 1]),
            'device_name.unique' => __('messages.name_exists'),
            'device_address.required' => __('messages.field_required'),
            'device_address.min' => __('messages.min_length', ['min' => 3]),
            'device_address.unique' => __('messages.name_exists'),
        ];
        $this->crud->setValidation($rules, $messages);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        CRUD::setEditView('vendor.backpack.crud.device_edit');
        $this->setupCreateOperation();
    }

    protected function update(Request $request)
    {
        $item = $this->crud->getCurrentEntry();

        $rules = [
            'device_type' => 'required',
            'device_name' => [
                'required',
                'min:1',
                Rule::unique('devices', 'device_name')->ignore($this->crud->getCurrentEntryId()),
            ],
            'device_address' => [
                'required',
                'min:3',
                Rule::unique('devices', 'device_address')->ignore($this->crud->getCurrentEntryId()),
            ],
        ];
        $messages = [
            'device_type.required' => 'The device type field is required.',
            'device_name.required' => 'The device name field is required.',
            'device_name.min' => 'Use more than 1 character.',
            'device_name.unique' => 'This device name already exists.',
            'device_address.required' => 'The device address field is required.',
            'device_address.min' => 'Use more than 3 characters.',
            'device_address.unique' => 'This device address already exists.',
        ];
        $request->validate($rules, $messages);
        try {
            $gateway_change_action = $request->gateway_change_action ?? null;

            if ($gateway_change_action) {
                if ($gateway_change_action === 'all') {
                    $devices = Device::all()->where('gateway', $item->gateway);
                    foreach ($devices as $device) {
                        if ($device->id === $item->id) {
                            continue;
                        } else {
                            $device->update([
                                'gateway' => $request->gateway,
                            ]);
                        }
                    }
                }
            }

            $item->update([
                'gateway' => $request->gateway,
                'device_name' => $request->device_name,
                'device_address' => $request->device_address,
                'device_type' => $request->device_type,
            ]);

            Alert::success(trans('backpack::crud.update_success'))->flash();

            $this->crud->setSaveAction();
            return $this->crud->performSaveAction($item->getKey());
        } catch (\Exception $e) {
            Log::error('Error updating device: ' . $e->getMessage());
            Alert::error('An error occurred while updating the device. Please try again later.')->flash();
            return redirect()->back()->withInput();
        }
    }
}
