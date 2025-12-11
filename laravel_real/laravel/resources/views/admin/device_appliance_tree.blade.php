@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card" style="background-color: #1a1c20; border-color: #3a3f47;">
                <div class="card-header" style="background-color: #23262b; border-color: #3a3f47; color: #e3e6e9;">
                    <h3 class="card-title" style="color: #e3e6e9;">{{ __('messages.devices') }} & {{ __('messages.appliances') }}</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" id="publishSelected">
                            <i class="la la-check"></i> {{ __('messages.publish_selected') }}
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
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
.publish-status.published {
    background: #d4edda;
    color: #155724;
}
.publish-status.not-published {
    background: #f8d7da;
    color: #721c24;
}
</style>

@push('after_scripts')
<script>
let devices = @json($devices);
let appliances = @json($appliances);

function renderDeviceTree() {
    const container = document.getElementById('deviceTree');
    
    devices.forEach(device => {
        const deviceAppliances = appliances.filter(a => a.device_id === device.id);
        
        const deviceGroup = document.createElement('div');
        deviceGroup.className = 'device-group';
        
        const deviceHeader = document.createElement('div');
        deviceHeader.className = 'device-header collapsed';
        deviceHeader.innerHTML = `
            <input type="checkbox" class="device-checkbox" data-device-id="${device.id}">
            <i class="la la-angle-down collapse-icon"></i>
            <span class="device-name">${device.device_name}</span>
            <span class="device-info">${device.device_type} | ${device.device_address}</span>
            <span class="badge badge-secondary">${deviceAppliances.length} {{ __('messages.appliances') }}</span>
        `;
        
        const applianceList = document.createElement('ul');
        applianceList.className = 'appliance-list';
        applianceList.style.display = 'none';
        
        deviceAppliances.forEach(appliance => {
            const li = document.createElement('li');
            li.className = 'appliance-item';
            li.innerHTML = `
                <input type="checkbox" class="appliance-checkbox" data-appliance-id="${appliance.id}" data-device-id="${device.id}">
                <span class="appliance-name">${appliance.appliance_name}</span>
                <span class="appliance-type">${appliance.appliance_type_name}</span>
                <span class="publish-status ${appliance.is_published ? 'published' : 'not-published'}">
                    ${appliance.is_published ? '{{ __('messages.published') }}' : '{{ __('messages.not_published') }}'}
                </span>
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
            text: '{{ __('messages.no_appliances_selected') }}'
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
                text: '{{ __('messages.publish_success') }}'
            }).show();
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error('Publish failed');
        }
    } catch (error) {
        new Noty({
            type: 'error',
            text: '{{ __('messages.publish_error') }}'
        }).show();
    }
});

// Initialize
renderDeviceTree();
</script>
@endpush
@endsection
