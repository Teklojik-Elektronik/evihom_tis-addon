@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card" style="background-color: #1a1c20; border-color: #3a3f47;">
                <div class="card-header" style="background-color: #23262b; border-color: #3a3f47; color: #e3e6e9;">
                    <h3 class="card-title" style="color: #e3e6e9;">{{ __('messages.devices') }} & {{ __('messages.appliances') }}</h3>
                </div>
                <div class="card-body p-0">
                    <div style="padding: 15px 20px; background-color: #23262b; border-bottom: 1px solid #3a3f47; display: flex; gap: 10px; align-items: center;">
                        <button type="button" class="btn btn-primary" id="publishSelected">
                            <i class="la la-check"></i> {{ __('messages.publish_selected') }}
                        </button>
                        <div style="flex: 1; max-width: 300px;">
                            <input type="text" id="searchBox" class="form-control" placeholder="Search..." style="background-color: #2d3035; border-color: #3a3f47; color: #e3e6e9;">
                        </div>
                    </div>
                    <div id="deviceTree"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.device-group {
    border-bottom: 1px solid #3a3f47;
}
.device-header {
    padding: 15px 20px;
    background: #2d3035;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #e3e6e9;
}
.device-header:hover {
    background: #3a3f47;
}
.device-checkbox {
    width: 18px;
    height: 18px;
}
.collapse-icon {
    transition: transform 0.3s;
    font-size: 14px;
    color: #6c757d;
}
.collapsed .collapse-icon {
    transform: rotate(-90deg);
}
.device-name {
    font-weight: 600;
    flex: 1;
    color: #e3e6e9;
}
.device-info {
    color: #9da5b0;
    font-size: 0.9em;
}
.appliance-list {
    padding: 0;
    margin: 0;
    list-style: none;
    background: #23262b;
}
.appliance-item {
    padding: 12px 20px 12px 60px;
    border-top: 1px solid #3a3f47;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #e3e6e9;
}
.appliance-item:hover {
    background: #2d3035;
}
.appliance-checkbox {
    width: 16px;
    height: 16px;
}
.appliance-name {
    flex: 1;
    color: #e3e6e9;
}
.appliance-type {
    color: #e3e6e9;
    font-size: 0.85em;
    padding: 2px 8px;
    background: #3a3f47;
    border-radius: 3px;
}
.publish-status {
    font-size: 0.85em;
    padding: 2px 8px;
    border-radius: 3px;
}
.delete-device-btn,
.delete-appliance-btn {
    margin-left: auto;
    opacity: 0.8;
    transition: opacity 0.2s;
}
.delete-device-btn:hover,
.delete-appliance-btn:hover {
    opacity: 1;
}
.publish-status.published {
    background: #d4edda;
    color: #155724;
}
.publish-status.not-published {
    background: #f8d7da;
    color: #721c24;
}
</style>

<!-- Data passed from server -->
<script type="application/json" id="translations-data">
    @json([
        'appliances' => __('messages.appliances'),
        'published' => __('messages.published'),
        'notPublished' => __('messages.not_published'),
        'noAppliancesSelected' => __('messages.no_appliances_selected'),
        'publishSuccess' => __('messages.publish_success'),
        'publishError' => __('messages.publish_error'),
        'confirmDeleteDevice' => __('messages.confirm_delete_device'),
        'confirmDeleteAppliance' => __('messages.confirm_delete_appliance'),
        'deviceDeleted' => __('messages.device_deleted'),
        'applianceDeleted' => __('messages.appliance_deleted'),
        'deleteError' => __('messages.delete_error')
    ])
</script>
<script type="application/json" id="devices-data">
    @json($devices)
</script>
<script type="application/json" id="appliances-data">
    @json($appliances)
</script>

@push('after_scripts')
<script>
// Parse data from JSON script tags
const translations = JSON.parse(document.getElementById('translations-data').textContent);
const devicesData = JSON.parse(document.getElementById('devices-data').textContent);
const appliancesData = JSON.parse(document.getElementById('appliances-data').textContent);

function renderDeviceTree() {
    const container = document.getElementById('deviceTree');
    
    devicesData.forEach(device => {
        const deviceAppliances = appliancesData.filter(a => a.device_id === device.id);
        
        const deviceGroup = document.createElement('div');
        deviceGroup.className = 'device-group';
        
        const deviceHeader = document.createElement('div');
        deviceHeader.className = 'device-header collapsed';
        deviceHeader.innerHTML = `
            <input type="checkbox" class="device-checkbox" data-device-id="${device.id}">
            <i class="la la-angle-down collapse-icon"></i>
            <span class="device-name">${device.device_name}</span>
            <span class="device-info">${device.device_type} | ${device.device_address}</span>
            <span class="badge badge-secondary">${deviceAppliances.length} ${translations.appliances}</span>
            <button class="btn btn-sm btn-danger delete-device-btn" data-device-id="${device.id}" onclick="deleteDevice(${device.id}, event)">
                <i class="la la-trash"></i>
            </button>
        `;
        
        const applianceList = document.createElement('ul');
        applianceList.className = 'appliance-list';
        applianceList.style.display = 'none';
        
        deviceAppliances.forEach(appliance => {
            const li = document.createElement('li');
            li.className = 'appliance-item';
            const publishStatusText = appliance.is_published ? translations.published : translations.notPublished;
            const publishStatusClass = appliance.is_published ? 'published' : 'not-published';
            
            li.innerHTML = `
                <input type="checkbox" class="appliance-checkbox" data-appliance-id="${appliance.id}" data-device-id="${device.id}">
                <span class="appliance-name">${appliance.appliance_name}</span>
                <span class="appliance-type">${appliance.appliance_type_name}</span>
                <span class="publish-status ${publishStatusClass}">
                    ${publishStatusText}
                </span>
                <button class="btn btn-sm btn-danger delete-appliance-btn" data-appliance-id="${appliance.id}" onclick="deleteAppliance(${appliance.id}, event)">
                    <i class="la la-trash"></i>
                </button>
            `;
            applianceList.appendChild(li);
        });
        
        deviceHeader.addEventListener('click', (e) => {
            if (e.target.type !== 'checkbox') {
                deviceHeader.classList.toggle('collapsed');
                if (applianceList.style.display === 'none') {
                    applianceList.style.display = 'block';
                } else {
                    applianceList.style.display = 'none';
                }
            }
        });
        
        // Device checkbox logic
        const deviceCheckbox = deviceHeader.querySelector('.device-checkbox');
        deviceCheckbox.addEventListener('change', (e) => {
            const checked = e.target.checked;
            applianceList.querySelectorAll('.appliance-checkbox').forEach(cb => {
                cb.checked = checked;
            });
        });
        
        // Appliance checkbox logic
        applianceList.addEventListener('change', (e) => {
            if (e.target.classList.contains('appliance-checkbox')) {
                const allChecked = Array.from(applianceList.querySelectorAll('.appliance-checkbox'))
                    .every(cb => cb.checked);
                const someChecked = Array.from(applianceList.querySelectorAll('.appliance-checkbox'))
                    .some(cb => cb.checked);
                
                deviceCheckbox.checked = allChecked;
                deviceCheckbox.indeterminate = someChecked && !allChecked;
            }
        });
        
        deviceGroup.appendChild(deviceHeader);
        deviceGroup.appendChild(applianceList);
        container.appendChild(deviceGroup);
    });
}

// Publish selected appliances
document.getElementById('publishSelected').addEventListener('click', async () => {
    const selectedAppliances = Array.from(document.querySelectorAll('.appliance-checkbox:checked'))
        .map(cb => cb.dataset.applianceId);
    
    if (selectedAppliances.length === 0) {
        new Noty({
            type: 'warning',
            text: translations.noAppliancesSelected
        }).show();
        return;
    }
    
    try {
        const response = await fetch('/api/appliances/publish-multiple', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ appliance_ids: selectedAppliances })
        });
        
        if (response.ok) {
            new Noty({
                type: 'success',
                text: translations.publishSuccess
            }).show();
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error('Publish failed');
        }
    } catch (error) {
        new Noty({
            type: 'error',
            text: translations.publishError
        }).show();
    }
});

// Delete Device Function
async function deleteDevice(deviceId, event) {
    event.stopPropagation();
    
    if (!confirm(translations.confirmDeleteDevice)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/devices/${deviceId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (response.ok) {
            new Noty({
                type: 'success',
                text: translations.deviceDeleted
            }).show();
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error('Delete failed');
        }
    } catch (error) {
        new Noty({
            type: 'error',
            text: translations.deleteError
        }).show();
    }
}

// Delete Appliance Function
async function deleteAppliance(applianceId, event) {
    event.stopPropagation();
    
    if (!confirm(translations.confirmDeleteAppliance)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/appliances/${applianceId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (response.ok) {
            new Noty({
                type: 'success',
                text: translations.applianceDeleted
            }).show();
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error('Delete failed');
        }
    } catch (error) {
        new Noty({
            type: 'error',
            text: translations.deleteError
        }).show();
    }
}

// Search Filter Function
document.getElementById('searchBox').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const deviceGroups = document.querySelectorAll('.device-group');
    
    deviceGroups.forEach(group => {
        const deviceHeader = group.querySelector('.device-header');
        const deviceName = deviceHeader.querySelector('.device-name').textContent.toLowerCase();
        const deviceInfo = deviceHeader.querySelector('.device-info').textContent.toLowerCase();
        const applianceItems = group.querySelectorAll('.appliance-item');
        
        let deviceMatch = deviceName.includes(searchTerm) || deviceInfo.includes(searchTerm);
        let hasVisibleAppliance = false;
        
        // Filter appliances
        applianceItems.forEach(item => {
            const applianceName = item.querySelector('.appliance-name').textContent.toLowerCase();
            const applianceType = item.querySelector('.appliance-type').textContent.toLowerCase();
            
            if (searchTerm === '' || applianceName.includes(searchTerm) || applianceType.includes(searchTerm)) {
                item.style.display = '';
                hasVisibleAppliance = true;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show device if it matches OR has visible appliances
        if (searchTerm === '' || deviceMatch || hasVisibleAppliance) {
            group.style.display = '';
        } else {
            group.style.display = 'none';
        }
    });
});

// Initialize
renderDeviceTree();
</script>
@endpush
@endsection
