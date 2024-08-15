@php
use App\Enums\PermissionsEnum;
@endphp

@extends('components.layout')

@section('page-title')
@parent
<div class="flex justify-center align-middle">
    <button class="ui icon button" onclick="window.location='{{ route('devicemanagement') }}'">
        <i class="arrow left icon"></i>
    </button>
    <span class="self-center ml-2">Device Management > Device Details</span>
</div>
@endsection

@section('content')
<div class="flex flex-col h-full px-5 py-7 overflow-y-auto overflow-x-hidden">
    <div class="ui stackable equal width grid">
        <div class="row">
            <div class="column">
                <div class="ui icon message">
                    <img src="{{ asset('imgs/bulb.png') }}" alt="icon" class="ui image w-14 h-14 mr-4">
                    <div class="content">
                        <div class="header">
                            LightMaster Controller
                        </div>
                        <p>Manages all connected components. Configure the device settings and preferences here.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="column !pt-0 !pr-0 !pb-0">
                <div class="flex flex-col justify-center h-full w-full">
                    <div class="flex items-center">
                        <h2 class="ui header" id="deviceNameDisplay">{{ $device->ExternalDeviceName }}</h2>
                        @can([PermissionsEnum::CAN_EDIT_DEVICE_NAME, PermissionsEnum::ALL_ACCESS_TO_DEVICE])
                        <button class="ml-2" id="editDeviceNameButton">
                            <i class="pencil icon"></i>
                        </button>
                        @endcan
                    </div>


                    <!-- Hidden input and save button -->
                    <div class="ui input flex items-center mt-2 w-1/2 !hidden" id="editDeviceNameSection">
                        <input type="text" id="deviceNameInput" class="ui small input !mr-2"
                            value="{{ $device->ExternalDeviceName }}">
                        <button class="ui small blue button ml-2" id="saveDeviceNameButton">Save</button>
                    </div>

                    <span class="text-sm text-gray-500 mt-2 ml-1">{{ $device->deviceStatus->Status }}</span>
                </div>
            </div>
            <div class="column">
                <div class="flex flex-col justify-center h-full w-full">
                    <span class="text-sm text-gray-500">Added date: {{ $device->created_at->format('m/d/Y') }}</span>
                    <span class="text-sm text-gray-500 mt-1">Operation date: {{ $device->OperationDate ?
                        $device->OperationDate->format('m/d/Y') : 'N/A' }}</span>
                    <span class="text-sm text-gray-500 mt-1">IP Address: {{ $device->IPAddress ? $device->IPAddress :
                        'N/A' }}</span>
                </div>
            </div>
            <div class="column">
                @if($device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PENDING)
                @can([PermissionsEnum::CAN_DEPLOY_DEVICE, PermissionsEnum::ALL_ACCESS_TO_DEVICE])
                <form action="{{ route('device.deploy', $device->DeviceID) }}" method="POST" class="!float-right">
                    @csrf
                    <button type="submit" id="btnDeploy"
                        class="ui green small compact labeled icon button float-right !mt-2" @if(empty($baseTime) ||
                        $deviceTimes->isEmpty()) disabled @endif>
                        <i class="rocket icon"></i>
                        Deploy
                    </button>
                </form>
                @endcan
                @else
                @if ($device->deviceStatus->Status == App\Enums\DeviceStatusEnum::DISABLED)
                <div class="tooltip-container !float-right">
                    <form id="enable-form" action="{{ route('device.enable', $device->DeviceID) }}" method="POST">
                        @csrf
                        <button id="btnEnable" data-id="{{ $device->DeviceID }}"
                            class="ui green small compact labeled icon button float-right !mt-2">
                            <i class="power off icon"></i>
                            Enable
                        </button>
                        @if ($device->deviceStatus->Status == App\Enums\DeviceStatusEnum::RUNNING ||
                        $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PENDING)
                        <span class="tooltip-text !text-sm">Cannot use this function because the device is
                            running or on pause</span>
                        @endif
                    </form>
                </div>
                @else
                <div class="tooltip-container !float-right">
                    @can([PermissionsEnum::CAN_DISABLE_DEVICE, PermissionsEnum::ALL_ACCESS_TO_DEVICE])
                    <form id="disable-form" action="{{ route('device.disable', $device->DeviceID) }}" method="POST">
                        @csrf
                        <button id="btnDisable" data-id="{{ $device->DeviceID }}"
                            class="ui small compact labeled icon button float-right !mt-2" {{
                            ($device->deviceStatus->Status
                            == App\Enums\DeviceStatusEnum::RUNNING ||
                            $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PENDING ||
                            $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PAUSE ||
                            $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::STARTFREE) ? 'disabled' : ''
                            }}>
                            <i class="power off icon"></i>
                            Disable
                        </button>
                        @if ($device->deviceStatus->Status == App\Enums\DeviceStatusEnum::RUNNING ||
                        $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PENDING ||
                        $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PAUSE ||
                        $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::STARTFREE)
                        <span class="tooltip-text !text-sm">Cannot use this function because the device is
                            running or on pause</span>
                        @endif
                    </form>
                    @endcan
                </div>
                @endif
                @endif

                @can([PermissionsEnum::CAN_DELETE_DEVICE, PermissionsEnum::ALL_ACCESS_TO_DEVICE])
                <div class="tooltip-container !float-right">
                    <button id="btnDeleteDevice" data-id="{{ $device->DeviceID }}"
                        class="ui red small compact labeled icon button float-right !mt-2" {{
                        ($device->deviceStatus->Status
                        ==
                        App\Enums\DeviceStatusEnum::RUNNING ||
                        $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PAUSE ||
                        $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::STARTFREE) ? 'disabled' : '' }}>
                        <i class="trash alternate icon"></i>
                        Delete
                    </button>
                    @if ($device->deviceStatus->Status == App\Enums\DeviceStatusEnum::RUNNING ||
                    $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PAUSE ||
                    $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::STARTFREE)
                    <span class="tooltip-text !text-sm">Cannot use this function because the device is running or on
                        pause</span>
                    @endif
                </div>
                @endcan

                @if ($device->deviceStatus->Status != App\Enums\DeviceStatusEnum::DISABLED)
                @can([PermissionsEnum::CAN_TRIGGER_FREE_LIGHT, PermissionsEnum::ALL_ACCESS_TO_DEVICE])
                <div class="tooltip-container !float-right">
                    <button id="btnFreeLight" data-id="{{ $device->DeviceID }}"
                        class="ui {{ $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::STARTFREE ? 'red' : 'green' }} small compact labeled icon button float-right !mt-2"
                        {{ ($device->deviceStatus->Status ==
                        App\Enums\DeviceStatusEnum::RUNNING ||
                        $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PAUSE ||
                        $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PENDING) ? 'disabled' : '' }}>
                        <i class="lightbulb icon"></i>
                        {{ $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::STARTFREE ? 'Stop light' : 'Free
                        light' }}
                    </button>
                    @if ($device->deviceStatus->Status == App\Enums\DeviceStatusEnum::RUNNING ||
                    $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PAUSE)
                    <span class="tooltip-text !text-sm">Cannot use this function because the device is running or on
                        pause</span>
                    @endif
                </div>
                @endcan
                @endif

                @if ($device->deviceStatus->Status != App\Enums\DeviceStatusEnum::DISABLED)
                <div class="tooltip-container !float-right">
                    <button id="btnTestLight" data-id="{{ $device->DeviceID }}"
                        class="ui orange small compact labeled icon button float-right !mt-2" {{
                        ($device->deviceStatus->Status ==
                        App\Enums\DeviceStatusEnum::RUNNING ||
                        $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PAUSE||
                        $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::STARTFREE) ? 'disabled' : '' }}>
                        <i class="zhihu icon"></i>
                        Test light
                    </button>
                    @if ($device->deviceStatus->Status == App\Enums\DeviceStatusEnum::RUNNING ||
                    $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PAUSE||
                    $device->deviceStatus->Status == App\Enums\DeviceStatusEnum::STARTFREE)
                    <span class="tooltip-text !text-sm">Cannot use this function because the device is
                        running or on pause</span>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @if($device->deviceStatus->Status == App\Enums\DeviceStatusEnum::PENDING)
        <div class="row">
            <div class="column">
                <div id="pendingConfigurationMessage" class="ui visible orange small message">
                    <i class="exclamation circle icon"></i>
                    Please complete the Base Time Configuration and add at least one Time Increment to validate the
                    device for deployment.
                </div>
            </div>
        </div>
        @endif
        <div class="ui divider"></div>
        <div class="row">
            <div class="column">
                <h5 class="ui header">Base Time Configuration</h5>
            </div>
        </div>
        <div class="row">
            <div class="column">
                <form class="ui form" action="{{ route('device-time.base') }}" method="POST">
                    @csrf
                    <div class="ui three column stackable grid">
                        <div class="four wide column">
                            <div class="field">
                                <label>Base start time</label>
                            </div>
                            <div class="ui fluid small right labeled input">
                                <input type="number" name="base_time" value="{{ $baseTime ? $baseTime->Time : '' }}"
                                    placeholder="60" required>
                                <div class="ui basic label">
                                    minutes
                                </div>
                            </div>
                        </div>
                        <div class="four wide column">
                            <div class="field">
                                <label>Base start rate</label>
                            </div>
                            <div class="ui fluid small right labeled input">
                                <input type="number" step="0.01" name="base_rate"
                                    value="{{ $baseTime ? $baseTime->Rate : '' }}" placeholder="30.00" required>
                                <div class="ui basic label">
                                    PHP
                                </div>
                            </div>
                        </div>
                        @can([PermissionsEnum::CAN_EDIT_DEVICE_BASE_TIME, PermissionsEnum::ALL_ACCESS_TO_DEVICE])
                        <div class="three wide column">

                            <div class="field">
                                <label class="invisible">search</label>
                            </div>
                            <button type="submit" class="ui fluid small blue button">Save</button>
                        </div>
                        @endcan

                    </div>
                    <input type="hidden" name="device_id" value="{{ $device->DeviceID }}">
                </form>
            </div>
        </div>

        <div class="ui divider"></div>
        <div class="row">
            <div class="column">
                <h5 class="ui header">Time Increments</h5>
            </div>
        </div>

        <div class="row">
            <div class="column">
                <div class="ui cards">
                    @foreach ($deviceTimes as $deviceTime)
                    <div class="ui card w-1/3 m-2">
                        <a id="increment-notification-banner-{{ $deviceTime->DeviceTimeID }}"
                            class="ui gray tag label !text-black increment-notification-banner {{ $deviceTime->Active ? '!hidden' : '!block' }}">
                            Disabled
                        </a>
                        <div class="content flex justify-between items-center">
                            <div class="flex flex-col">
                                <span class="font-semibold">{{ convertMinutesToHoursAndMinutes($deviceTime->Time)
                                    }}</span>
                                <span class="text-gray-500">PHP {{ $deviceTime->Rate }}</span>
                            </div>
                            <div class="flex items-center flex-grow justify-end">
                                @can([PermissionsEnum::CAN_EDIT_DEVICE_INCREMENTS,
                                PermissionsEnum::ALL_ACCESS_TO_DEVICE])
                                <button class="ui icon button !bg-transparent !border-none p-2 edit-increment-button"
                                    data-id="{{ $deviceTime->DeviceTimeID }}" data-time="{{ $deviceTime->Time }}"
                                    data-rate="{{ $deviceTime->Rate }}" title="Edit">
                                    <img src="{{ asset('imgs/edit.png') }}" alt="edit" class="w-5 h-5">
                                </button>
                                @endcan
                                @can([PermissionsEnum::CAN_DISABLE_DEVICE_INCREMENTS,
                                PermissionsEnum::ALL_ACCESS_TO_DEVICE])
                                <button id="btnDisableIncrement"
                                    class="ui icon button !bg-transparent !border-none p-2 update-increment-status-button"
                                    data-id="{{ $deviceTime->DeviceTimeID }}"
                                    data-device-id="{{ $deviceTime->DeviceID }}"
                                    data-status="{{ $deviceTime->Active ? '1' : '0' }}"
                                    title="{{ $deviceTime->Active ? 'Disable' : 'Enable' }}">
                                    @if ($deviceTime->Active)
                                    <img src="{{ asset('imgs/disable.png') }}" data-id="{{ $deviceTime->DeviceTimeID }}"
                                        alt="disable" class="w-5 h-5">
                                    @else
                                    <img src="{{ asset('imgs/enable.png') }}" data-id="{{ $deviceTime->DeviceTimeID }}"
                                        alt="disable" class="w-5 h-5">
                                    @endif

                                </button>
                                @endcan
                                @can([PermissionsEnum::CAN_DISABLE_DEVICE_INCREMENTS,
                                PermissionsEnum::ALL_ACCESS_TO_DEVICE])
                                <button class="ui icon button !bg-transparent !border-none p-2 delete-increment-button"
                                    data-id="{{ $deviceTime->DeviceTimeID }}" title="Delete">
                                    <img src="{{ asset('imgs/delete.png') }}" alt="delete" class="w-5 h-5">
                                </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @can([PermissionsEnum::CAN_ADD_DEVICE_INCREMENTS,
                    PermissionsEnum::ALL_ACCESS_TO_DEVICE])
                    <div class="ui card w-1/3 !h-24 m-2 !bg-teal-100">
                        <button id="addIncrementButton" class="ui small w-full h-full">
                            <div class="flex justify-center align-middle">
                                <img src="{{ asset('imgs/plus.png') }}" alt="add" class="w-10 h-10 mr-3">
                                <span class="self-center font-bold">Add Increment</span>
                            </div>
                        </button>
                    </div>
                    @endcan

                </div>
            </div>
        </div>

        @can([PermissionsEnum::CAN_EDIT_DEVICE_BASE_TIME, PermissionsEnum::ALL_ACCESS_TO_DEVICE])
        <div class="ui divider"></div>
        <div class="row">
            <div class="column">
                <h5 class="ui header">Watchdog Configuration</h5>
            </div>
        </div>
        <div class="row">
            <div class="column">
                <div class="ui icon message warning">
                    <div class="content">
                        <div class="header">
                            What is <i>Watchdog</i>?
                        </div>
                        <p>This process involves the device performing self-maintenance and periodic checks at regular
                            time intervals. It ensures the device's proper functioning by continuously monitoring its
                            status and making necessary adjustments.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="column">
                <form class="ui form" method="POST">
                    @csrf
                    <div class="ui three column stackable grid">
                        <div class="four wide column">
                            <div class="field">
                                <label>Watchdog interval</label>
                            </div>
                            <div class="ui fluid small right labeled input">
                                <input type="number" name="txt_watchdogInterval" id="txt_watchdogInterval"
                                    value="{{ $device->WatchdogInterval ? $device->WatchdogInterval : 0 }}"
                                    placeholder="60" required>
                                <div class="ui basic label">
                                    minutes
                                </div>
                            </div>
                        </div>

                        <div class="three wide column">

                            <div class="field">
                                <label class="invisible">search</label>
                            </div>
                            <button id="saveWatchdogInterval" type="button"
                                class="ui fluid small blue button">Save</button>
                        </div>


                    </div>
                    <input type="hidden" name="device_id" value="{{ $device->DeviceID }}">
                </form>
            </div>
        </div>
        @endcan


        @can([PermissionsEnum::CAN_EDIT_DEVICE_BASE_TIME, PermissionsEnum::ALL_ACCESS_TO_DEVICE])
        <div class="ui divider"></div>
        <div class="row">
            <div class="column">
                <h5 class="ui header">Remaining time reminder notification</h5>
            </div>
        </div>
        <div class="row">
            <div class="column">
                <div class="ui icon message warning">
                    <div class="content">
                        <div class="header">

                        </div>
                        <p>A notification that the customer only has N minutes before the transaction time ends. Set to
                            0 to if notification is not needed.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="column">
                <form class="ui form" method="POST">
                    @csrf
                    <div class="ui three column stackable grid">
                        <div class="four wide column">
                            <div class="field">
                                <label>Remaining time notification</label>
                            </div>
                            <div class="ui fluid small right labeled input">
                                <input type="number" name="txt_remainingTime" id="txt_remainingTime"
                                    value="{{ $device->RemainingTimeNotification ? $device->RemainingTimeNotification : 0 }}"
                                    placeholder="60" required>
                                <div class="ui basic label">
                                    minutes
                                </div>
                            </div>
                        </div>


                        <div class="three wide column">

                            <div class="field">
                                <label class="invisible">search</label>
                            </div>
                            <button id="saveRemainingTime" type="button"
                                class="ui fluid small blue button">Save</button>
                        </div>


                    </div>
                    <input type="hidden" name="device_id" value="{{ $device->DeviceID }}">
                </form>
            </div>
        </div>
        @endcan

        @can([PermissionsEnum::CAN_VIEW_DEVICE_SPECIFIC_RATE_USAGE_REPORT,
        PermissionsEnum::ALL_ACCESS_TO_DEVICE])
        <div class="ui divider"></div>
        <div class="row">
            <div class="column">
                <h5 class="ui header">Rate and Usage Reports</h5>
            </div>
        </div>

        <div class="row">
            <div class="column">
                <div class="flex space-x-1">
                    <button id="monthlyButton"
                        class="px-4 py-2 bg-blue-500 text-white rounded-l text-sm hover:bg-blue-600 focus:outline-none">Monthly</button>
                    <button id="dailyButton"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-r text-sm hover:bg-gray-300 focus:outline-none">Daily</button>

                </div>

                <div class="text-center">
                    <h3 id="chartTitle">Monthly Rate and Usage</h3>
                    <p id="chartSubtitle" class="text-muted text-gray-400">For the current year of {{ date('Y') }}</p>
                    <canvas id="rateChart" width="400" height="160"></canvas>
                </div>
            </div>

        </div>
        @endcan

        @can([PermissionsEnum::CAN_VIEW_DEVICE_SPECIFIC_TIME_TRANSACTION_REPORT,
        PermissionsEnum::ALL_ACCESS_TO_DEVICE])
        <div class="ui divider"></div>
        <div class="row">
            <div class="column">
                <h5 class="ui header">Time Transactions for today ({{ \Carbon\Carbon::today()->format('F j, Y') }})
                </h5>
            </div>
        </div>

        <div class="row">
            <div class="column">
                <div id="deviceTblcontainer" class="ui celled table-container max-h-[500px] overflow-y-auto">
                    <table class="ui celled table w-full">
                        <thead class="sticky top-0 bg-white">
                            <tr>
                                <th class="px-4 py-2">Transaction</th>
                                <th class="px-4 py-2">Time</th>
                                <th class="px-4 py-2">Duration</th>
                                <th class="px-4 py-2">Rate</th>
                                <th class="px-4 py-2">Triggered By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $totalRate = 0;
                            $totalDuration = 0;

                            @endphp
                            @foreach($rptDeviceTimeTransactions as $transaction)
                            @php

                            if ($transaction->TransactionType == 'End')
                            {
                            $totalRate += $transaction->Rate;
                            $totalDuration += $transaction->Duration;
                            }


                            $creatorName = 'N/A';

                            if ($transaction->CreatedByUserId === 999999)
                            {
                            $creatorName = 'Device';
                            }
                            else
                            {
                            if ($transaction->creator) {
                            if ($transaction->creator->UserID > 0) {
                            $creatorName = $transaction->creator->FirstName . ' ' . $transaction->creator->LastName;
                            }
                            }
                            }

                            @endphp

                            @if ($transaction->TransactionType == 'End')
                            <tr>
                                <td>{{ $transaction->TransactionType }}</td>
                                <td>{{ $transaction->Time->format('F d, Y h:i:s A') }}</td>
                                <td colspan="2" class="font-bold">{{ $transaction->StoppageType }} Stoppage</td>
                                <td>{{ $creatorName }}</td>
                            </tr>
                            <tr class="font-bold">
                                <td colspan="2" class="font-bold">Total Duration and Rate</td>
                                <td>{{ convertMinutesToHoursAndMinutes($transaction->Duration) }}</td>
                                <td colspan="2">PHP {{ number_format($transaction->Rate, 2) }}</td>
                            </tr>
                            @elseif ($transaction->TransactionType == 'Pause')
                            <tr>
                                <td>{{ $transaction->TransactionType }}</td>
                                <td>{{ $transaction->Time->format('F d, Y h:i:s A') }}</td>
                                <td colspan="2" class="font-bold">Remaining time: {{
                                    convertSecondsToTimeFormat($transaction->Duration) }}</td>
                                <td>{{ $creatorName }}</td>
                            </tr>
                            @elseif ($transaction->TransactionType == 'Start Free')
                            <tr>
                            <tr>
                                <td>{{ $transaction->TransactionType }}</td>
                                <td>{{ $transaction->Time->format('F d, Y h:i:s A') }}</td>
                                <td colspan="2">
                                    <a href="javascript:void(0);"
                                        onclick="showReasonModal('{{ $transaction->Reason }}')">
                                        Show reason
                                        <!-- Shorten the display -->
                                    </a>
                                </td>
                                <td>{{ $creatorName }}</td>
                            </tr>
                            </tr>
                            @elseif ($transaction->TransactionType == 'End Free')
                            <tr>
                                <td>{{ $transaction->TransactionType }}</td>
                                <td>{{ $transaction->Time->format('F d, Y h:i:s A') }}</td>
                                <td colspan="2">Duration: {{ convertSecondsToTimeFormat($transaction->Duration) }}</td>
                                <td>{{ $creatorName }}</td>
                            </tr>
                            @elseif ($transaction->TransactionType == 'Resume')
                            <tr>
                                <td>{{ $transaction->TransactionType }}</td>
                                <td colspan="3">{{ $transaction->Time->format('F d, Y h:i:s A') }}</td>
                                <td>{{ $creatorName }}</td>
                            </tr>
                            @else
                            <tr>
                                <td>{{ $transaction->TransactionType }}</td>
                                <td>{{ $transaction->Time->format('F d, Y h:i:s A') }}</td>
                                <td>{{ $transaction->Duration }}</td>
                                <td>{{ number_format($transaction->Rate, 2) }}</td>
                                <td>{{ $creatorName }}</td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                        <tfoot class="sticky bottom-0 bg-white !font-bold">
                            <tr class="!font-bold bg-cyan-100 h-20">
                                <td colspan="2" class="!font-bold">Overall Duration and Rate Total</td>
                                <td class="!font-bold">{{ convertMinutesToHoursAndMinutes($totalDuration) }}</td>
                                <td colspan="2" class="!font-bold">PHP {{ number_format($totalRate, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endcan

    </div>
</div>
<div id="reasonModal" class="ui modal">
    <div class="header">Reason</div>
    <div class="content">
        <p id="reasonContent"></p>
    </div>
    <div class="actions">
        <button class="ui button" onclick="$('#reasonModal').modal('hide')">Close</button>
    </div>
</div>
<x-modals.add-increment-modal :device="$device" />
<x-modals.delete-increment-confirmation-modal />
<x-modals.delete-device-confirmation-modal />
<x-modals.free-light-reason-modal :device="$device" />

@endsection

<script>
    function showReasonModal(reason) {
        const reasonMdl = document.getElementById('reasonContent');
        if (reasonMdl)
        {
            reasonMdl.textContent = reason;
            $('#reasonModal').modal('show');    
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const deviceID = {{ $device->DeviceID }}; // Pass the DeviceID to JavaScript
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const disableForm = document.getElementById('disable-form');
        const enableForm = document.getElementById('enable-form');

        const monthlyLabels = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        const ctx = document.getElementById("rateChart").getContext("2d");
        const chart = new Chart(ctx, getConfig("monthly"));
        fetchChartData("monthly", deviceID);


        document.getElementById("dailyButton").addEventListener("click", () => {
            updateChartTitle("daily");
            fetchChartData("daily", deviceID);
            document.getElementById("dailyButton").classList.add("bg-blue-500", "text-white");
            document.getElementById("monthlyButton").classList.remove("bg-blue-500", "text-white");
            document.getElementById("dailyButton").classList.remove("bg-gray-200", "text-gray-800");
            document.getElementById("monthlyButton").classList.add("bg-gray-200", "text-gray-800");
        });

        document.getElementById("monthlyButton").addEventListener("click", () => {
            updateChartTitle("monthly");
            fetchChartData("monthly", deviceID);
            document.getElementById("monthlyButton").classList.add("bg-blue-500", "text-white");
            document.getElementById("dailyButton").classList.remove("bg-blue-500", "text-white");
            document.getElementById("monthlyButton").classList.remove("bg-gray-200", "text-gray-800");
            document.getElementById("dailyButton").classList.add("bg-gray-200", "text-gray-800");
        });

        $('#btnTestLight').on('click', function() {
            var deviceId = $(this).data('id');

            // Disable buttons
            var buttons = $('#btnDeploy, #btnDisable, #btnDeleteDevice, #btnFreeLight, #btnTestLight');
            // buttons.prop('disabled', true);
            showLoading();

            $.ajax({
                url: `/api/device/${deviceId}/test`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Use a timer to re-enable buttons after 10 seconds
                        setTimeout(function() {
                            // buttons.prop('disabled', false);
                            hideLoading();
                        }, 9000); // 10000 milliseconds = 10 seconds

                    } else {
                        alert('Failed to test device: ' + response.message);

                        // Re-enable buttons immediately if testing fails
                        // buttons.prop('disabled', false);
                        hideLoading();
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + xhr.responseText);

                    // Re-enable buttons immediately on error
                    // buttons.prop('disabled', false);
                    hideLoading();
                }
            });
        });

        

        if (disableForm)
        {
            document.getElementById('disable-form').addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                showLoading();

                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = '{{ route('devicemanagement') }}';
                        sessionStorage.setItem('toastMessage', JSON.stringify({message: data.message, type: 'success'}));
                        hideLoading();
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.log('Fetch error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                });
            });
        }

        if (enableForm)
        {
            document.getElementById('enable-form').addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                showLoading();

                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = '{{ route('devicemanagement') }}';
                        sessionStorage.setItem('toastMessage', JSON.stringify({message: data.message, type: 'success'}));
                        hideLoading();
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.log('Fetch error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                });
            });
        }

        var tableContainer = document.getElementById('deviceTblcontainer');
        tableContainer.scrollTop = tableContainer.scrollHeight;

        function updateChartTitle(viewType) {
            const currentDate = new Date(); // Get the current date
            const currentYear = currentDate.getFullYear(); // Extract the year
            const currentMonth = currentDate.toLocaleString('default', { month: 'long' });
            
            const titleText = viewType === "daily" ? "Daily Rate and Usage" : "Monthly Rate and Usage";
            const subtitleText = viewType === "daily" ? "For the current Month of " + currentMonth : "For the current year of " + currentYear;
            document.getElementById("chartTitle").textContent = titleText;
            document.getElementById("chartSubtitle").textContent = subtitleText;
            chart.update();
        }

        function getConfig(viewType) {
            const labels = viewType === "daily" ? getDaysInCurrentMonth() : monthlyLabels;

            return {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Rate (PHP)",
                            data: [],
                            backgroundColor: 'rgb(126, 183, 237)', // Add transparency back
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            borderRadius: {
                                topLeft: 5, // Add border radius to top-left corner
                                topRight: 5, // Add border radius to top-right corner
                            },
                            yAxisID: 'y',
                            order: 2, // Order of bar graph
                        },
                        {
                            label: "Usage (Minutes)",
                            data: [],
                            type: 'line',
                            backgroundColor: 'rgb(66, 66, 71)', // Line fill color with transparency
                            borderColor: 'rgb(66, 66, 71)', // Red line
                            borderWidth: 2,
                            pointRadius: 4, // Increase node size
                            pointBackgroundColor: 'rgb(66, 66, 71)', // Red node
                            yAxisID: 'y1',
                            order: 1, // Higher order for line graph to bring it in front
                            tension: 0.4, // Smooth the line
                            cubicInterpolationMode: 'monotone'
                        }
                    ],
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Rate (PHP)'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false // Only want the grid lines for one axis
                            },
                            title: {
                                display: true,
                                text: 'Usage (minutes)'
                            }
                        }
                    }
                }
            };
        }

        function fetchChartData(viewType, deviceID) {
            const url =
                viewType === "daily"
                    ? `/reports/device/usage/daily/${deviceID}`
                    : `/reports/device/usage/monthly/${deviceID}`;

            fetch(url)
                .then((response) => response.json())
                .then((data) => {
                    console.log(viewType, data);
                    const labels = Object.keys(data);
                    const rates = Object.values(data).map((entry) => entry.totalRate);
                    const usage = Object.values(data).map((entry) => entry.totalDuration);

                    updateChart(viewType, labels, rates, usage);
                })
                .catch((error) => console.error("Error fetching data:", error));
        }

        function updateChart(viewType, labels, rates, usage) {
            chart.data.labels = labels;
            chart.data.datasets[0].data = rates;
            chart.data.datasets[1].data = usage;
            chart.update();
        }

        function getDaysInCurrentMonth() {
            const date = new Date();
            const days = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
            return Array.from({ length: days }, (_, i) => i + 1);
        }

        const updateButtons = document.querySelectorAll('.update-increment-status-button');

        updateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const incrementId = this.getAttribute('data-id');
                const deviceId = this.getAttribute('data-device-id');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Toggle the status (e.g., if 1 means active and 0 means disabled)
                const newStatus = this.dataset.status === '1' ? '0' : '1';
                const newStatusText = newStatus === '1' ? 'Enable' : 'Disable';

                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('device_id', deviceId);
                formData.append('incrementStatus', newStatus);

                fetch(`/device-time/increment/status/${incrementId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    }
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        return response.text().then(text => { throw new Error(text); });
                    }
                })
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        // Toggle the text on the button and update the banner
                        this.dataset.status = newStatus;
                        this.title = newStatusText;
                        const incBanner = document.querySelector(`#increment-notification-banner-${incrementId}`);
                        const imageElement = document.querySelector(`img[data-id="${incrementId}"]`);
                        
                        if (newStatus === '0')
                        {
                            incBanner.classList.add('!block'); // Remove the '!block' class
                            incBanner.classList.remove('!hidden'); 
                            imageElement.src = '{{ asset('imgs/enable.png') }}';
                        }
                        else
                        {
                            incBanner.classList.remove('!block'); // Remove the '!block' class
                            incBanner.classList.add('!hidden'); 
                            imageElement.src = '{{ asset('imgs/disable.png') }}';
                        }
                        
                    } 
                    else {
                        showToast(data.message || 'An error occurred.', 'error');
                    }
                })
                .catch(error => {
                    console.log('Fetch error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                });
            });
        });

        const freeLightButton = document.getElementById('btnFreeLight');
        
        freeLightButton.addEventListener('click', function() {
            const action = this.textContent.trim().includes('Free') ? 'startFree' : 'stopFree';

            if (action === 'startFree')
            {
                $(freeLightModal).modal('show');
            }
            else
            {
                showLoading();
                fetch(`/device/${deviceID}/stopfree`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        window.location.href = '/device';
                    } else {
                        alert('Failed to stop free light. Please try again.');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        });

        const deviceNameDisplay = document.getElementById('deviceNameDisplay');
        const editDeviceNameButton = document.getElementById('editDeviceNameButton');
        const editDeviceNameSection = document.getElementById('editDeviceNameSection');
        const deviceNameInput = document.getElementById('deviceNameInput');
        const saveDeviceNameButton = document.getElementById('saveDeviceNameButton');

        editDeviceNameButton.addEventListener('click', function () {
            deviceNameDisplay.style.display = 'none';
            editDeviceNameButton.style.display = 'none';
            editDeviceNameSection.classList.remove('!hidden');
            deviceNameInput.focus();
        });

        saveDeviceNameButton.addEventListener('click', function () {
            const newDeviceName = deviceNameInput.value.trim();
            if (newDeviceName) {
                showLoading();

                setTimeout(() => {
                    // AJAX request to update the device name in the database
                    fetch('/device/update/name', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' // Ensure you have this token for Laravel
                        },
                        body: JSON.stringify({
                            external_device_id: '{{ $device->DeviceID }}',
                            external_device_name: newDeviceName
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoading(); // Hide loading after the request completes

                        if (data.success) {
                            // Update the display
                            deviceNameDisplay.textContent = newDeviceName;
                            // Hide input and save button, show display
                            deviceNameDisplay.style.display = 'block';
                            editDeviceNameButton.style.display = 'inline-block';
                            editDeviceNameSection.classList.add('!hidden');
                        } else {
                            alert('Failed to update the device name.');
                        }
                    })
                    .catch(error => {
                        hideLoading(); // Hide loading in case of an error
                        console.error('Error:', error);
                    });
                }, 2000);
            } else {
                alert('Please enter a valid device name.');
            }
        });


        const saveWatchdogIntervalButton = document.getElementById('saveWatchdogInterval');
        const txt_watchdogInterval = document.getElementById('txt_watchdogInterval');

        if (saveWatchdogIntervalButton) {
            saveWatchdogIntervalButton.addEventListener('click', function () {
                const newDeviceWDInterval = parseInt(txt_watchdogInterval.value, 30);  
                
                if (newDeviceWDInterval > 0) {
                    showLoading();

                    setTimeout(() => {
                        fetch('/device/update/watchdog', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Ensure you have this token for Laravel
                            },
                            body: JSON.stringify({
                                deviceId: '{{ $device->DeviceID }}',
                                watchdogInterval: newDeviceWDInterval
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoading(); // Hide loading after the request completes

                            if (data.success) {
                                showToast(data.message, 'success');
                            } else {
                                showToast(data.message || 'An error occurred.', 'error');
                            }
                        })
                        .catch(error => {
                            hideLoading(); // Hide loading in case of an error
                            console.log('Fetch error:', error);
                            showToast('An error occurred. Please try again.', 'error');
                        });
                    }, 2000);
                } else {
                    alert('Please enter a valid interval greater than 0.');
                }
            });
        }


        const saveRemainingTime = document.getElementById('saveRemainingTime');
        const txt_remainingTime = document.getElementById('txt_remainingTime');

        if (saveRemainingTime) {
            saveRemainingTime.addEventListener('click', function () {
                const newRemainingTime = parseInt(txt_remainingTime.value, 0);  
                
                if (newRemainingTime > -1) {
                    showLoading();

                    setTimeout(() => {
                        fetch('/device/update/remainingtime', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Ensure you have this token for Laravel
                            },
                            body: JSON.stringify({
                                deviceId: '{{ $device->DeviceID }}',
                                remainingTime: newRemainingTime
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoading(); // Hide loading after the request completes

                            if (data.success) {
                                showToast(data.message, 'success');
                            } else {
                                showToast(data.message || 'An error occurred.', 'error');
                            }
                        })
                        .catch(error => {
                            hideLoading(); // Hide loading in case of an error
                            console.log('Fetch error:', error);
                            showToast('An error occurred. Please try again.', 'error');
                        });
                    }, 2000);
                } else {
                    alert('Please enter a valid interval greater than -1.');
                }
            });
        }
    });
</script>