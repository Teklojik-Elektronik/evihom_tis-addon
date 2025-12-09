<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function saveProject()
    {
        $tables = ['devices', 'appliances', "appliance_channels"]; // Specify the tables you want to dump
        $data = [];

        foreach ($tables as $table) {
            $data[$table] = DB::table($table)->get();
        }

        $json = json_encode($data);
        $filePath = 'projects/TIS_Homeassistant_Project.json';
        Storage::disk('local')->put($filePath, $json);

        return response()->download(storage_path("app/{$filePath}"));
    }

    public function loadProject(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $file = $request->file('file');
        $json = file_get_contents($file->getRealPath());
        $data = json_decode($json, true);

        foreach ($data as $table => $rows) {
            if($table === "appliances"){
                foreach ($rows as $key => $row) {
                    // remove the "appliance_class" key from the row
                    unset($rows[$key]['appliance_class']);
                }
            }
            DB::table($table)->truncate();
            DB::table($table)->insert($rows);
        }

        Alert::add("success", "Project Loaded Successfully")->flash();

        return redirect()->route('device.index');
    }
}
