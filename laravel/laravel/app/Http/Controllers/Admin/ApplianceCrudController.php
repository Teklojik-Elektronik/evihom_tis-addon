<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;

/**
 * Class ApplianceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ApplianceCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Appliance::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/appliance');
        CRUD::setEntityNameStrings('appliance', 'appliances');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // set columns from db columns.
        CRUD::addButtonFromModelFunction('top', 'publish', 'publish');
        CRUD::column('device_id')->type('select2')->entity('deviceId')->attribute("device_name")->model('App\Models\Device');
        CRUD::column('appliance_type')->type('select2')->entity('applianceType')->attribute("appliance_type_name")->model('App\Models\ApplianceType');

        CRUD::removeColumn('is_protected');
        CRUD::removeColumn('min');
        CRUD::removeColumn('max');
        CRUD::removeColumn('settings');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setCreateView('vendor.backpack.crud.appliance_create');

        CRUD::setFromDb();
        CRUD::addField([
            'name' => 'appliance_type',
            'type' => 'select2',
            'label' => 'Appliance Type',
            'attribute' => 'appliance_type_name',
            'model' => \App\Models\ApplianceType::class,
        ]);
        CRUD::addField([
            'name' => 'device_id',
            'type' => 'select2',
            'label' => 'Device',
            'attribute' => 'device_name',
            'model' => \App\Models\Device::class,
        ]);
        CRUD::addField([
            'name' => 'min',
            'type' => 'number',
            'label' => 'Minimum Value',
            'attributes' => [
                'placeholder' => 'Type the minimum value for analog sensor only',
            ]
        ]);
        CRUD::addField([
            'name' => 'max',
            'type' => 'number',
            'label' => 'Maximum Value',
            'attributes' => [
                'placeholder' => 'Type the maximum value for analog sensor only',
            ]
        ]);

        CRUD::addField([
            'name' => 'exchange_command',
            'type' => 'switch',
            'label' => 'Exchange Command',
        ]);

        CRUD::addField([
            'name' => 'min_capacity',
            'type' => 'number',
            'label' => 'Minimum Capacity',
            'attributes' => [
                'placeholder' => 'Type the minimum capacity for the analog sensor',
            ]
        ]);

        CRUD::addField([
            'name' => 'max_capacity',
            'type' => 'number',
            'label' => 'Maximum Capacity',
            'attributes' => [
                'placeholder' => 'Type the maximum capacity for the analog sensor',
            ]
        ]);

        CRUD::addField([
            "name" => "universal_type",
            "type" => "select_from_array",
            "options" => ["0" => "Off", "1" => "On"],
            "label" => "Universal Switch's Type",
            "allows_null" => false,
            'default' => "0",
            'hint' => "It would be useful for video switcher device"
        ]);

        // remove
        CRUD::removeField('is_protected');
        CRUD::removeField('settings');
    }

    public function store()
    {
        // Get request data
        $request = request();

        $excludedFields = ['_token', '_method', '_save_action', '_http_referrer', 'appliance_name', 'appliance_type', 'device_id'];
        $filteredData = $request->except($excludedFields);
        $settings = json_encode($filteredData);
        $request->merge(['settings' => $settings]);

        // Define base validation rules
        $rules = [
            'appliance_name' => 'required|string|max:255',
            'device_id' => 'required|exists:devices,id',
            'appliance_type' => 'required|string',
            'settings' => 'nullable|json',
        ];

        // Check if appliance_type is 'analog_sensor' and apply additional validation
        if ($request->input('appliance_type') === '11') {
            $rules['min'] = ['required', 'integer', 'min:0', 'max:100', 'lte:max'];
            $rules['max'] = ['required', 'integer', 'min:0', 'max:100', 'gte:min'];
            $rules['min_capacity'] = ['required', 'integer', 'min:0', 'lte:max_capacity'];
            $rules['max_capacity'] = ['required', 'integer', 'min:0', 'gte:min_capacity'];
        } else {
            $rules['min'] = ['nullable', 'integer', 'min:0', 'max:100', 'lte:max'];
            $rules['max'] = ['nullable', 'integer', 'min:0', 'max:100', 'gte:min'];
            $rules['min_capacity'] = ['nullable', 'integer', 'min:0', 'lte:max_capacity'];
            $rules['max_capacity'] = ['nullable', 'integer', 'min:0', 'gte:min_capacity'];
        }

        // Define error messages
        $messages = [
            'appliance_name.required' => 'The appliance name is required.',
            'device_id.required' => 'The device is required.',
            'device_id.exists' => 'The selected device does not exist.',
            'appliance_type.required' => 'The appliance type is required.',
            'min.required' => 'The minimum value is required for analog sensors.',
            'max.required' => 'The maximum value is required for analog sensors.',
            'min.lte' => 'The minimum value must be less than or equal to the maximum value.',
            'max.gte' => 'The maximum value must be greater than or equal to the minimum value.',
            'max_capacity.required' => 'The maximum capacity is required for analog sensors.',
            'min_capacity.required' => 'The minimum capacity is required for analog sensors.',
            'min_capacity.lte' => 'The minimum capacity must be less than or equal to the maximum capacity.',
            'max_capacity.gte' => 'The maximum capacity must be greater than or equal to the minimum capacity.',
        ];

        // Validate the request
        $validatedData = $request->validate($rules, $messages);
        $item = $this->crud->create($validatedData);

        // Optionally, you can add a success message (using Backpack's Alert system)
        Alert::success('Record created successfully.')->flash();

        // Determine where to redirect after save
        $saveAction = $request->input('save_action') ?? 'save_and_back';

        if ($saveAction === 'save_and_edit') {
            // Redirect to the edit page for the newly created record
            return redirect()->to($this->crud->route . '/' . $item->getKey() . '/edit');
        } elseif ($saveAction === 'save_and_new') {
            // Redirect back to the create form
            return redirect()->to($this->crud->route . '/create');
        } else {
            // Default redirect to the list view
            return redirect()->to($this->crud->route);
        }
    }


    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $appliance = $this->crud->getCurrentEntry();
        $settings = json_decode($appliance->settings);
        CRUD::setEditView('vendor.backpack.crud.appliance_edit');
        $this->setupCreateOperation();

        CRUD::addField([
            'name' => 'exchange_command',
            'type' => 'switch',
            'label' => 'Exchange Command',
            'value' => $settings->exchange_command ?? '0',
        ]);

        CRUD::addField([
            'name' => 'min_capacity',
            'type' => 'number',
            'label' => 'Minimum Capacity',
            'value' => $settings->min_capacity ?? null,
            'attributes' => [
                'placeholder' => 'Type the minimum capacity for the analog sensor',
            ],
        ]);

        CRUD::addField([
            'name' => 'max_capacity',
            'type' => 'number',
            'label' => 'Maximum Capacity',
            'value' => $settings->max_capacity ?? null,
            'attributes' => [
                'placeholder' => 'Type the maximum capacity for the analog sensor',
            ],
        ]);

        CRUD::addField([
            "name" => "universal_type",
            "type" => "select_from_array",
            "options" => ["0" => "Off", "1" => "On"],
            "label" => "Universal Switch's Type",
            "value" => $settings->universal_type ?? null,
            "allows_null" => false,
            'default' => "0",
            'hint' => "It would be useful for video switcher device"
        ]);
    }

    protected function update()
    {
        // Get request data
        $request = request();

        $excludedFields = ['_token', '_method', '_save_action', '_http_referrer', 'appliance_name', 'appliance_type', 'device_id', 'id'];
        $filteredData = $request->except($excludedFields);
        $settings = json_encode($filteredData);
        $request->merge(['settings' => $settings]);

        // Define base validation rules
        $rules = [
            'appliance_name' => 'required|string|max:255',
            'device_id' => 'required|exists:devices,id',
            'appliance_type' => 'required|string',
            'settings' => 'nullable|json',
        ];

        // Check if appliance_type is 'analog_sensor' and apply additional validation
        if ($request->input('appliance_type') === '11') {
            $rules['min'] = ['required', 'integer', 'min:0', 'max:100', 'lte:max'];
            $rules['max'] = ['required', 'integer', 'min:0', 'max:100', 'gte:min'];
            $rules['min_capacity'] = ['required', 'integer', 'min:0', 'lte:max_capacity'];
            $rules['max_capacity'] = ['required', 'integer', 'min:0', 'gte:min_capacity'];
        } else {
            $rules['min'] = ['nullable', 'integer', 'min:0', 'max:100', 'lte:max'];
            $rules['max'] = ['nullable', 'integer', 'min:0', 'max:100', 'gte:min'];
            $rules['min_capacity'] = ['nullable', 'integer', 'min:0', 'lte:max_capacity'];
            $rules['max_capacity'] = ['nullable', 'integer', 'min:0', 'gte:min_capacity'];
        }

        // Define error messages
        $messages = [
            'appliance_name.required' => 'The appliance name is required.',
            'device_id.required' => 'The device is required.',
            'device_id.exists' => 'The selected device does not exist.',
            'appliance_type.required' => 'The appliance type is required.',
            'min.required' => 'The minimum value is required for analog sensors.',
            'max.required' => 'The maximum value is required for analog sensors.',
            'min.lte' => 'The minimum value must be less than or equal to the maximum value.',
            'max.gte' => 'The maximum value must be greater than or equal to the minimum value.',
            'max_capacity.required' => 'The maximum capacity is required for analog sensors.',
            'min_capacity.required' => 'The minimum capacity is required for analog sensors.',
            'min_capacity.lte' => 'The minimum capacity must be less than or equal to the maximum capacity.',
            'max_capacity.gte' => 'The maximum capacity must be greater than or equal to the minimum capacity.',
        ];

        // Validate the request
        $validatedData = $request->validate($rules, $messages);
        $item = $this->crud->update($request->id, $validatedData);

        // Optionally, you can add a success message (using Backpack's Alert system)
        Alert::success('Record updated successfully.')->flash();

        // Determine where to redirect after save
        $saveAction = $request->input('save_action') ?? 'save_and_back';

        if ($saveAction === 'save_and_edit') {
            // Redirect to the edit page for the updated record
            return redirect()->to($this->crud->route . '/' . $item->getKey() . '/edit');
        } elseif ($saveAction === 'save_and_new') {
            // Redirect back to the create form
            return redirect()->to($this->crud->route . '/create');
        } else {
            // Default redirect to the list view
            return redirect()->to($this->crud->route);
        }
    }

    protected function setupShowOperation()
    {
        CRUD::setFromDb();
        $appliance = $this->crud->getCurrentEntry();
        $appliance->appliance_type == "11" ? Null : CRUD::removeColumn('min');
        $appliance->appliance_type == "11" ? Null : CRUD::removeColumn('max');

        CRUD::column('device_id')->type('select2')->entity('deviceId')->attribute("device_name")->model('App\Models\Device');
        CRUD::column('appliance_type')->type('select2')->entity('applianceType')->attribute("appliance_type_name")->model('App\Models\ApplianceType');
    }
}
