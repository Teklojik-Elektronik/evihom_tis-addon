<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SettingsRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class SettingsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SettingsCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Settings::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/settings');
        CRUD::setEntityNameStrings('settings', 'settings');
        CRUD::denyAccess(['create', 'delete']);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // Add language selector widget at the top
        Widget::add([
            'type' => 'view',
            'view' => 'admin.partials.language_selector',
        ])->to('before_content');

        CRUD::addColumn([
            'name' => 'key',
            'type' => 'text',
            'label' => 'Key',
        ]);
        CRUD::addColumn([
            'name' => 'value',
            'type' => 'text',
            'label' => 'Value',
            'limit' => 35
        ]);
        CRUD::addButtonFromView(
            'top',
            'update_button',
            'vendor.backpack.crud.buttons.update_button',
            'beginning'
        );

        try {
            $resp = Http::get('http://homeassistant.local:8123/api/get_key')->json();
            if ($resp !== null && isset($resp['key'])) {
                $mac_address = $resp['key'];
                Log::info("mac address fetched {$mac_address}");
                $response = Http::withToken(config('license.api_key'))
                    ->get(config('license.server_url') . 'get-serial', ['mac_address' => $mac_address]);

                Log::info("response from license: {$response}");
                if ($response !== null && $response->status() === 200 && isset($response->json()['serial_number'])) {
                    $serial_number = $response->json()['serial_number'];
                    if ($serial_number) {
                        $fetched = true;
                        Widget::add([
                            'type'       => 'card',
                            'wrapper' => ['class' => 'col-sm-6 col-md-4 mx-auto'],
                            'class'   => 'card text-success text-center fs-2 fw-bolder',
                            'content'    => [
                                'header' => 'Serial Number:',
                                'body'   => $serial_number,
                            ]
                        ])->to('after_content');
                    } else {
                        Log::error("Serial Number not provided");
                        $fetched = false;
                    }
                } else {
                    Log::error("Error happened fetching the serial number");
                    $fetched = false;
                }
            } else {
                Log::error("Error happened fetching the mac address");
                $fetched = false;
            }
        } catch (\Exception $e) {
            Log::error("Something went wrong!");
            $fetched = false;
        }
        if (!$fetched) {
            Widget::add([
                'type'       => 'card',
                'wrapper' => ['class' => 'col-sm-6 col-md-4 mx-auto'],
                'class'   => 'card text-danger text-center fw-bolder',
                'content'    => [
                    'header' => "Couldn't get the Serial Number",
                    "body" => "please try again later",
                ]
            ])->to('after_content');
        }
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(SettingsRequest::class);
        CRUD::setFromDb(); // set fields from db columns.

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        $this->crud->enableTabs();
        $this->crud->setFromDb();
        $item = $this->crud->getCurrentEntry();
        if ($item) {
            if ($item->key === 'electricity_bill') {
                CRUD::removeColumn('value');
                CRUD::removeColumn('key');

                CRUD::addColumn([
                    'name' => 'key',
                    'type' => 'text',
                    'tab' => 'Summer Rates',
                    'label' => 'Key',
                ]);

                CRUD::addColumn([
                    'name' => 'key2',
                    'type' => 'text',
                    'tab' => 'Winter Rates',
                    'value' => $item->key,
                    'label' => 'Key',
                ]);

                $summer_rates = json_decode($item->value)->summer_rates;
                $winter_rates = json_decode($item->value)->winter_rates;

                $summer_rates = collect($summer_rates)->values()->map(function ($rate, $index) use ($summer_rates) {
                    $next = $summer_rates[$index + 1] ?? null;
                    return [
                        'tier' => $index + 1,
                        'min_kw' => $rate->min_kw,
                        'max_kw' => $next ? $next->min_kw : '∞',
                        'price_per_kw' => $rate->price_per_kw,
                    ];
                })->toArray();

                CRUD::addColumn([
                    'name' => 'summer_rates',
                    'type' => 'table',
                    'label' => 'Summer Rates',
                    'tab' => 'Summer Rates',
                    'columns' => [
                        'tier' => 'Tier',
                        'min_kw' => 'from (KW)',
                        'max_kw' => 'to (KW)',
                        'price_per_kw' => 'Price per KW',
                    ],
                    'value' => function () use ($summer_rates) {
                        return $summer_rates;
                    },
                ]);

                $winter_rates = collect($winter_rates)->values()->map(function ($rate, $index) use ($winter_rates) {
                    $next = $winter_rates[$index + 1] ?? null;
                    return [
                        'tier' => $index + 1,
                        'min_kw' => $rate->min_kw,
                        'max_kw' => $next ? $next->min_kw : '∞',
                        'price_per_kw' => $rate->price_per_kw,
                    ];
                })->toArray();

                CRUD::addColumn([
                    'name' => 'winter_rates',
                    'type' => 'table',
                    'label' => 'Winter Rates',
                    'tab' => 'Winter Rates',
                    'columns' => [
                        'tier' => 'Tier',
                        'min_kw' => 'from (KW)',
                        'max_kw' => 'to (KW)',
                        'price_per_kw' => 'Price per KW',
                    ],
                    'value' => function () use ($winter_rates) {
                        return $winter_rates;
                    },
                ]);
            }
        }
    }
}
