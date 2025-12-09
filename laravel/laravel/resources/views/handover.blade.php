@extends(backpack_view('blank'))

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none"
        bp-section="page-header">
        <div class="row d-flex justify-content-between" style="width: 100%">
            <div class="col-md-6">
                <h1 class="text-capitalize mb-0" bp-section="page-heading">TIS Handover Sheet</h1>
            </div>
            <div class="col-lg-5"></div>
            <div class="col-md-1 text-right">
                <img src="{{ asset('assets/img/tis_logo.jpg') }}" alt="TIS Logo">
            </div>
        </div>
    </section>
@endsection
@php
    $devices = \App\Models\Device::all();
@endphp

@section('content')
    <style>
        .handover-content {
            font-family: Arial, sans-serif;
        }

        .device-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .device-header th {
            padding: 10px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            border-left: none;
            border-right: none;
        }

        .appliance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .appliance-header th {
            padding: 10px;
            border-left: none;
            border-right: none;
        }

        .appliance-row td {
            padding: 10px;
            border-left: none;
            border-right: none;
            border-top: none;
            border-bottom: none;
        }

        .device-header {
            background-color: red;
            color: white;
        }

        .channel-type-header {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
            text-align: center;
            padding: 8px;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }

        .channel-header {
            background-color: #333;
            color: white;
            text-align: center;
        }

        .channel-header th {
            padding: 10px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        .appliance-header {
            background-color: #333;
            color: white;
        }

        .appliance-row td {
            padding: 10px;
            background-color: #f2f2f2;
            color: black;
        }
    </style>
    <div class="handover-content">
        @foreach ($devices as $device)
            <table class="device-table">
                <thead class="device-header">
                    <tr>
                        <th class="device-name">Name: {{ $device->device_name }}</th>
                        <th class="device-type">Type: {{ $device->deviceType->device_type_name }}</th>
                        <th class="device-mac">Address: {{ $device->gateway }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Get all appliance channels for this device
                        $all_appliance_channels = collect();
                        foreach ($device->appliances as $appliance) {
                            $all_appliance_channels = $all_appliance_channels->concat($appliance->applianceChannels);
                        }

                        // Filter output and input channels
                        $output_channels = $all_appliance_channels->filter(function ($channel) {
                            return $channel->channel_name == "Output Channel";
                        });

                        $input_channels = $all_appliance_channels->filter(function ($channel) {
                            return $channel->channel_name == "Input Channel";
                        });

                        // Filter other channels
                        $other_channels = $all_appliance_channels->filter(function ($channel) {
                            return $channel->channel_name != "Output Channel" && $channel->channel_name != "Input Channel";
                        });
                    @endphp

                    @if ($all_appliance_channels->isNotEmpty())
                        <tr class="channel-header">
                            <th colspan="3" class="text-center">Appliance Channels</th>
                        </tr>
                    @else
                        <tr class="channel-header">
                            <th colspan="3" class="text-center">There Are No Aplliances For This Device...</th>
                        </tr>
                    @endif

                    @if ($output_channels->count() > 0)
                        <tr>
                            <td colspan="3" class="channel-type-header">Output Channels</td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <table class="appliance-table">
                                    <tr class="appliance-header">
                                        <th class="appliance-name">Appliance Name</th>
                                        <th class="appliance-type">Appliance Type</th>
                                        <th class="channel-name">Channel Name</th>
                                        <th class="channel-name">Channel Number</th>
                                    </tr>
                                    @foreach ($output_channels as $appliance_channel)
                                        <tr class="appliance-row">
                                            <td>{{ $appliance_channel->applianceId->appliance_name }}</td>
                                            <td>{{ $appliance_channel->applianceId->applianceType->appliance_type_name }}</td>
                                            <td>{{ $appliance_channel->channel_name }}</td>
                                            <td>{{ $appliance_channel->channel_number }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if ($input_channels->count() > 0)
                        <tr>
                            <td colspan="3" class="channel-type-header">Input Channels</td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <table class="appliance-table">
                                    <tr class="appliance-header">
                                        <th class="appliance-name">Appliance Name</th>
                                        <th class="appliance-type">Appliance Type</th>
                                        <th class="channel-name">Channel Name</th>
                                        <th class="channel-name">Channel Number</th>
                                    </tr>
                                    @foreach ($input_channels as $appliance_channel)
                                        <tr class="appliance-row">
                                            <td>{{ $appliance_channel->applianceId->appliance_name }}</td>
                                            <td>{{ $appliance_channel->applianceId->applianceType->appliance_type_name }}</td>
                                            <td>{{ $appliance_channel->channel_name }}</td>
                                            <td>{{ $appliance_channel->channel_number }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if ($other_channels->count() > 0)
                        <tr>
                            <td colspan="3" class="channel-type-header">Other Channels</td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <table class="appliance-table">
                                    <tr class="appliance-header">
                                        <th class="appliance-name">Appliance Name</th>
                                        <th class="appliance-type">Appliance Type</th>
                                        <th class="channel-name">Channel Name</th>
                                        <th class="channel-name">Channel Number</th>
                                    </tr>
                                    @foreach ($other_channels as $appliance_channel)
                                        <tr class="appliance-row">
                                            <td>{{ $appliance_channel->applianceId->appliance_name }}</td>
                                            <td>{{ $appliance_channel->applianceId->applianceType->appliance_type_name }}</td>
                                            <td>{{ $appliance_channel->channel_name }}</td>
                                            <td>{{ $appliance_channel->channel_number }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @endforeach
    </div>
@endsection

@section('after_styles')
    {{-- DATA TABLES --}}
    @basset('https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css')
    @basset('https://cdn.datatables.net/fixedheader/3.3.1/css/fixedHeader.dataTables.min.css')
    @basset('https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css')

    {{-- CRUD LIST CONTENT - crud_list_styles stack --}}
    @stack('crud_list_styles')
@endsection

@section('after_scripts')
    {{-- CRUD LIST CONTENT - crud_list_scripts stack --}}
    @stack('crud_list_scripts')
@endsection
