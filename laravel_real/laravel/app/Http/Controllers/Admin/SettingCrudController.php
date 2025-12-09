<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SettingRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class SettingCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Setting::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/settings');
        CRUD::setEntityNameStrings(__('messages.settings'), __('messages.settings'));
    }

    protected function setupListOperation()
    {
        // Add language selector
        CRUD::addField([
            'name' => 'language_selector',
            'type' => 'custom_html',
            'value' => view('admin.partials.language_selector')->render(),
        ]);

        CRUD::addColumn([
            'name' => 'key',
            'label' => 'Setting',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'value',
            'label' => 'Value',
            'type' => 'text',
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupListOperation();
    }
}
