<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="submit">
                        <div class="row g-3">
                            {{-- Row 1 --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">Notification Type</label>
                                <input type="text" class="form-control" value="M1" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">Notification Description</label>
                                <input type="text" wire:model="notification_number" class="form-control"
                                    placeholder="Enter notification description">
                                @error('notification_number')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3 position-relative">
                                <label class="form-label mb-2">* Plant</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           wire:model.debounce.300ms="plant_search" 
                                           wire:click="$set('showPlantDropdown', true)"
                                           class="form-control"
                                           placeholder="Search plant..."
                                           autocomplete="off">
                                    
                                    <div wire:loading wire:target="plant_search" 
                                         class="spinner-border spinner-border-sm text-primary position-absolute" 
                                         style="top: 10px; right: 10px;"></div>

                                    @if ($showPlantDropdown && $plants->count() > 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1" style="z-index: 1050;">
                                            @foreach ($plants as $plant)
                                                <a href="#" 
                                                   wire:click.prevent="selectPlant({{ $plant->id }})"
                                                   class="dropdown-item">
                                                    {{ $plant->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @error('plant_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Row 2 --}}
                            <div class="col-md-4 mb-3 position-relative">
                                <label class="form-label mb-2">* Resource</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           wire:model.debounce.300ms="resource_search"
                                           wire:click="$set('showResourceDropdown', true)"
                                           class="form-control"
                                           placeholder="@if (!$plant_id) Pilih plant terlebih dahulu @else Search resource... @endif"
                                           @disabled(!$plant_id)
                                           autocomplete="off">
                                    
                                    <div wire:loading wire:target="resource_search" 
                                         class="spinner-border spinner-border-sm text-primary position-absolute" 
                                         style="top: 10px; right: 10px;"></div>

                                    @if ($showResourceDropdown && $resources->count() > 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1" style="z-index: 1050;">
                                            @foreach ($resources as $resource)
                                                <a href="#" 
                                                   wire:click.prevent="selectResource({{ $resource->id }})"
                                                   class="dropdown-item">
                                                    {{ $resource->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @error('resource_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3 position-relative">
                                <label class="form-label mb-2">* Functional Location</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           wire:model.debounce.300ms="func_search"
                                           wire:click="$set('showFuncDropdown', true)"
                                           class="form-control"
                                           placeholder="@if (!$resource_id) Pilih resource terlebih dahulu @else Search functional location... @endif"
                                           @disabled(!$resource_id)
                                           autocomplete="off">
                                    
                                    <div wire:loading wire:target="func_search" 
                                         class="spinner-border spinner-border-sm text-primary position-absolute" 
                                         style="top: 10px; right: 10px;"></div>

                                    @if ($showFuncDropdown && $functionalLocations->count() > 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1" style="z-index: 1050;">
                                            @foreach ($functionalLocations as $functionalLocation)
                                                <a href="#" 
                                                   wire:click.prevent="selectFunctionalLocation({{ $functionalLocation->id }})"
                                                   class="dropdown-item">
                                                    {{ $functionalLocation->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @error('functional_location_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3 position-relative">
                                <label class="form-label mb-2">* Equipment</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           wire:model.debounce.300ms="equipment_search"
                                           wire:click="$set('showEquipmentDropdown', true)"
                                           class="form-control"
                                           placeholder="@if (!$functional_location_id) Pilih functional location terlebih dahulu @else Search equipment... @endif"
                                           @disabled(!$functional_location_id)
                                           autocomplete="off">
                                    
                                    <div wire:loading wire:target="equipment_search" 
                                         class="spinner-border spinner-border-sm text-primary position-absolute" 
                                         style="top: 10px; right: 10px;"></div>

                                    @if ($showEquipmentDropdown && $equipments->count() > 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1" style="z-index: 1050;">
                                            @foreach ($equipments as $equipment)
                                                <a href="#" 
                                                   wire:click.prevent="selectEquipment({{ $equipment->id }})"
                                                   class="dropdown-item">
                                                    {{ $equipment->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @error('equipment_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Row 3 --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Planner Group</label>
                                <select wire:model="planner_group_id" class="form-control">
                                    <option value="">-- Pilih Planner Group --</option>
                                    @foreach ($planner_groups as $plannerGroup)
                                        <option value="{{ $plannerGroup->id }}">{{ $plannerGroup->name }}</option>
                                    @endforeach
                                </select>
                                @error('planner_group_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Malfunction Start</label>
                                <input type="datetime-local" wire:model="malfunction_start" 
                                       class="form-control @error('malfunction_start') is-invalid @enderror">
                                @error('malfunction_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Notification Date</label>
                                <input type="datetime-local" wire:model="notification_date" 
                                       class="form-control @error('notification_date') is-invalid @enderror">
                                @error('notification_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Row 4 --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Priority</label>
                                <select wire:model="priority" class="form-control">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                                @error('priority')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="3" 
                                          placeholder="Enter maintenance notes"></textarea>
                                @error('notes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Breakdown</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" wire:model="breakdown"
                                        id="breakdownSwitch">
                                    <label class="form-check-label"
                                        for="breakdownSwitch">{{ $breakdown ? 'Yes' : 'No' }}</label>
                                </div>
                            </div>

                            {{-- Row 5 --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">SPV Email</label>
                                <input type="email" readonly value="{{ $spv_email ?? '' }}"
                                    class="form-control bg-light"
                                    placeholder="Email will appear after selecting planner group">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Urgent Level</label>
                                <select wire:model="urgent_level" class="form-control">
                                    <option value="">-- Pilih Urgent Level --</option>
                                    <option value="Produksi & Delivery">Produksi & Delivery</option>
                                    <option value="Safety">Safety</option>
                                    <option value="Environment">Environment</option>
                                    <option value="Frequency">Frequency</option>
                                </select>
                                @error('urgent_level')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Department Requester</label>
                                <select wire:model="req_dept_id" class="form-control">
                                    <option value="">-- Pilih Department --</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('req_dept_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Row 6 --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Requester Name</label>
                                <select wire:model="req_user_id" class="form-control" @disabled(!$req_dept_id)>
                                    <option value="">-- Pilih Requester --</option>
                                    @foreach ($requesters as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('req_user_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="d-flex justify-content-end mt-4 gap-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading wire:target="submit" class="spinner-border spinner-border-sm me-1"></span>
                                <i wire:loading.remove wire:target="submit" class="fas fa-save me-1"></i>
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Click outside to close dropdowns --}}
    <div wire:click="hideDropdowns" class="position-fixed w-100 h-100 top-0 start-0" 
         style="z-index: 1040; display: {{ $showPlantDropdown || $showResourceDropdown || $showFuncDropdown || $showEquipmentDropdown ? 'block' : 'none' }};"></div>
</div>

@push('scripts')
<script>
    // Sweet Alert Success Handler
    document.addEventListener('livewire:initialized', function () {
        Livewire.on('show-success-alert', function (data) {
            swal({
                title: data[0].title,
                text: data[0].message,
                icon: "success",
                button: "OK",
            }).then((result) => {
                if (result) {
                    window.location.href = data[0].redirect;
                }
            });
        });

        Livewire.on('show-error-alert', function (data) {
            swal({
                title: data[0].title,
                text: data[0].message,
                icon: "error",
                button: "OK",
            });
        });
    });

    // Auto hide dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('.dropdown-menu.show');
        const inputs = document.querySelectorAll('input[wire\\:model*="search"]');
        
        let clickedOnInput = false;
        inputs.forEach(function(input) {
            if (input.contains(event.target)) {
                clickedOnInput = true;
            }
        });

        if (!clickedOnInput) {
            dropdowns.forEach(function(dropdown) {
                if (!dropdown.contains(event.target)) {
                    @this.call('hideDropdowns');
                }
            });
        }
    });
</script>
@endpush