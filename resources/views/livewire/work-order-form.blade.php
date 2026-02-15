<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="confirmSubmit">
                        <div class="row g-3">
                            {{-- Row 1 --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Notification Number</label>
                                <input type="text" wire:model.blur="notification_number" class="form-control"
                                    placeholder="Enter notification number">
                                @error('notification_number')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">Notification Description </label>
                                <input type="text" wire:model.blur="work_desc" class="form-control"
                                    placeholder="Enter notification description">
                                @error('work_desc')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3 position-relative">
                                <label class="form-label mb-2">* Plant</label>
                                <div class="position-relative">
                                    <input type="text" wire:model.live.debounce.500ms="plant_search"
                                        wire:focus="$set('showPlantDropdown', true)"
                                        class="form-control @error('plant_id') is-invalid @enderror"
                                        placeholder="Search plant..." autocomplete="off">

                                    <div wire:loading wire:target="plant_search"
                                        class="spinner-border spinner-border-sm text-primary position-absolute"
                                        style="top: 10px; right: 10px;"></div>

                                    @if ($showPlantDropdown && $plants->count() > 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1"
                                            style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                                            @foreach ($plants as $plant)
                                                <a href="#" wire:click.prevent="selectPlant({{ $plant->id }})"
                                                    class="dropdown-item">
                                                    {{ $plant->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @elseif ($showPlantDropdown && !empty($plant_search) && $plants->count() == 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1"
                                            style="z-index: 1050;">
                                            <span class="dropdown-item-text text-muted">Tidak ada plant ditemukan</span>
                                        </div>
                                    @endif
                                </div>
                                @error('plant_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Row 2 --}}
                            <div class="col-md-4 mb-3 position-relative">
                                <label class="form-label mb-2">* Resource</label>
                                <div class="position-relative">
                                    <input type="text" wire:model.live.debounce.500ms="resource_search"
                                        wire:focus="$set('showResourceDropdown', true)"
                                        class="form-control @error('resource_id') is-invalid @enderror"
                                        placeholder="@if (!$plant_id) Pilih plant terlebih dahulu @else Search resource... @endif"
                                        @disabled(!$plant_id) autocomplete="off">

                                    <div wire:loading wire:target="resource_search"
                                        class="spinner-border spinner-border-sm text-primary position-absolute"
                                        style="top: 10px; right: 10px;"></div>

                                    @if ($showResourceDropdown && $resources->count() > 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1"
                                            style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                                            @foreach ($resources as $resource)
                                                <a href="#"
                                                    wire:click.prevent="selectResource({{ $resource->id }})"
                                                    class="dropdown-item">
                                                    {{ $resource->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @elseif ($showResourceDropdown && !empty($resource_search) && $resources->count() == 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1"
                                            style="z-index: 1050;">
                                            <span class="dropdown-item-text text-muted">Tidak ada resource
                                                ditemukan</span>
                                        </div>
                                    @endif
                                </div>
                                @error('resource_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3 position-relative">
                                <label class="form-label mb-2">* Functional Location</label>
                                <div class="position-relative">
                                    <input type="text" wire:model.live.debounce.500ms="func_search"
                                        wire:focus="$set('showFuncDropdown', true)"
                                        class="form-control @error('functional_location_id') is-invalid @enderror"
                                        placeholder="@if (!$resource_id) Pilih resource terlebih dahulu @else Search functional location... @endif"
                                        @disabled(!$resource_id) autocomplete="off">

                                    <div wire:loading wire:target="func_search"
                                        class="spinner-border spinner-border-sm text-primary position-absolute"
                                        style="top: 10px; right: 10px;"></div>

                                    @if ($showFuncDropdown && $functionalLocations->count() > 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1"
                                            style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                                            @foreach ($functionalLocations as $functionalLocation)
                                                <a href="#"
                                                    wire:click.prevent="selectFunctionalLocation({{ $functionalLocation->id }})"
                                                    class="dropdown-item">
                                                    {{ $functionalLocation->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @elseif ($showFuncDropdown && !empty($func_search) && $functionalLocations->count() == 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1"
                                            style="z-index: 1050;">
                                            <span class="dropdown-item-text text-muted">Tidak ada functional location
                                                ditemukan</span>
                                        </div>
                                    @endif
                                </div>
                                @error('functional_location_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3 position-relative">
                                <label class="form-label mb-2">* Equipment</label>
                                <div class="position-relative">
                                    <input type="text" wire:model.live.debounce.500ms="equipment_search"
                                        wire:focus="$set('showEquipmentDropdown', true)"
                                        class="form-control @error('equipment_id') is-invalid @enderror"
                                        placeholder="@if (!$functional_location_id) Pilih functional location terlebih dahulu @else Search equipment... @endif"
                                        @disabled(!$functional_location_id) autocomplete="off">

                                    <div wire:loading wire:target="equipment_search"
                                        class="spinner-border spinner-border-sm text-primary position-absolute"
                                        style="top: 10px; right: 10px;"></div>

                                    @if ($showEquipmentDropdown && $equipments->count() > 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1"
                                            style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                                            @foreach ($equipments as $equipment)
                                                <a href="#"
                                                    wire:click.prevent="selectEquipment({{ $equipment->id }})"
                                                    class="dropdown-item">
                                                    {{ $equipment->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @elseif ($showEquipmentDropdown && !empty($equipment_search) && $equipments->count() == 0)
                                        <div class="dropdown-menu show position-absolute w-100 mt-1"
                                            style="z-index: 1050;">
                                            <span class="dropdown-item-text text-muted">Tidak ada equipment
                                                ditemukan</span>
                                        </div>
                                    @endif
                                </div>
                                @error('equipment_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Row 3 --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Planner Group</label>
                                <select wire:model.blur="planner_group_id"
                                    class="custom-select @error('planner_group_id') is-invalid @enderror">
                                    <option value="">-- Pilih Planner Group --</option>
                                    @foreach ($planner_groups as $plannerGroup)
                                        <option value="{{ $plannerGroup->id }}">{{ $plannerGroup->name }}</option>
                                    @endforeach
                                </select>
                                @error('planner_group_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Malfunction Start</label>
                                <input type="datetime-local" wire:model.blur="malfunction_start"
                                    class="form-control @error('malfunction_start') is-invalid @enderror">
                                @error('malfunction_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Notification Date</label>
                                <input type="datetime-local" wire:model.blur="notification_date"
                                    class="form-control @error('notification_date') is-invalid @enderror">
                                @error('notification_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Row 4 --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Priority</label>
                                <select wire:model.blur="priority"
                                    class="custom-select @error('priority') is-invalid @enderror">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Notes</label>
                                <textarea wire:model.blur="notes" class="form-control @error('notes') is-invalid @enderror" rows="2"
                                    placeholder="Enter maintenance notes"></textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-3 mr-3">* Breakdown</label>
                                <br>
                                {{-- <label for="breakdownSwitch" class="inline-flex items-center cursor-pointer mb-0">
                                    <div class="relative w-12 h-6 mr-2">
                                        <input type="checkbox" id="breakdownSwitch"
                                            class="peer absolute opacity-0 w-full h-full cursor-pointer z-10"
                                            wire:model.live="breakdown">
                                        <div
                                            class="absolute top-0 left-0 w-full h-full bg-gray-500 peer-checked:bg-blue-500 rounded-full transition-colors duration-300">
                                        </div>
                                        <div
                                            class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-md peer-checked:translate-x-6 transition-transform duration-300">
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500">Breakdown</span>
                                </label>       --}}
                                <label class="form-label switch">
                                    <input type="checkbox" wire:model.live="breakdown">
                                    <span class="slider">
                                        <span class="text yes">YES</span>
                                        <span class="text no">NO</span>
                                    </span>
                                </label>
                            </div>

                            {{-- Row 5 --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">SPV Email</label>
                                <input type="email" readonly value="{{ $spv_email ?? '' }}"
                                    class="form-control bg-light"
                                    placeholder="Email will appear after selecting department">
                                @if ($req_dept_id && !$spv_email)
                                    <div class="text-warning small mt-1">SPV tidak ditemukan</div>
                                @endif
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Urgent Level</label>
                                <select wire:model.blur="urgent_level"
                                    class="custom-select @error('urgent_level') is-invalid @enderror">
                                    <option value="">-- Pilih Urgent Level --</option>
                                    <option value="Produksi & Delivery">Produksi & Delivery</option>
                                    <option value="Safety">Safety</option>
                                    <option value="Environment">Environment</option>
                                    <option value="Frequency">Frequency</option>
                                </select>
                                @error('urgent_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Department Requester</label>
                                <select wire:model="req_dept_id"
                                    class="custom-select @error('req_dept_id') is-invalid @enderror" disabled>
                                    <option value="{{ auth()->user()->dept_id }}">
                                        {{ auth()->user()->department->name }}</option>
                                </select>
                                @error('req_dept_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Row 6 --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Requester Name</label>
                                <select wire:model="req_user_id"
                                    class="custom-select @error('req_user_id') is-invalid @enderror" disabled>
                                    <option value="{{ auth()->user()->id }}">
                                        {{ auth()->user()->name }} ({{ auth()->user()->email }})
                                    </option>
                                </select>
                                @error('req_user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="d-flex justify-content-end mt-4 gap-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-pill">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-pill" wire:loading.attr="disabled">
                                <span wire:loading wire:target="confirmSubmit"
                                    class="spinner-border spinner-border-sm me-1"></span>
                                <i wire:loading.remove wire:target="confirmSubmit" class="fas fa-save me-1"></i>
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
        style="z-index: 1040; display: {{ $showPlantDropdown || $showResourceDropdown || $showFuncDropdown || $showEquipmentDropdown ? 'block' : 'none' }};">
    </div>

    <!-- Loading Indicator -->
    <div wire:loading.delay wire:target='submit' class="position-fixed"
        style="top: 0; left: 0; width: 100%; height: 100%; background: rgba(178, 188, 202, 0.1); z-index: 9999;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="text-center">
                <div class="mb-3"
                    style="width: 3rem; height: 3rem; margin: 0 auto; border-radius: 50%; background: #1572e8; animation: grow 1.5s ease-in-out infinite;">
                </div>
                <h5 style="color: #1572e8;"></h5>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        @keyframes grow {

            0%,
            100% {
                transform: scale(0.5);
                opacity: 0.3;
            }

            50% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 70px;
            height: 28px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #f25961;
            transition: .3s;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 6px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        input:checked+.slider {
            background-color: #58ef44;
        }

        input:checked+.slider:before {
            transform: translateX(42px);
        }

        .text {
            font-size: 10px;
            font-weight: 600;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        .yes {
            color: white;
            opacity: 0;
        }

        .no {
            color: white;
            opacity: 1;
        }

        input:checked+.slider .yes {
            opacity: 1;
        }

        input:checked+.slider .no {
            opacity: 0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Sweet Alert Success Handler
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('show-success-alert', function(data) {
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

            Livewire.on('confirmSubmit', function(data) {
                swal({
                    title: "Are you sure?",
                    text: "You are about to submit this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Submit",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-success btn-pill"
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.submit();
                    }
                });
            });


        }, {
            once: true
        });

        document.addEventListener('livewire:navigated', function() {
            Livewire.on('show-success-alert', function(data) {
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

            Livewire.on('show-error-alert', function(data) {
                swal({
                    title: data[0].title,
                    text: data[0].message,
                    icon: "error",
                    button: "OK",
                });
            });

            Livewire.on('confirmSubmit', function(data) {
                swal({
                    title: "Are you sure?",
                    text: "You are about to submit this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Submit",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-success btn-pill"
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.submit();
                    }
                });
            });




        }, {
            once: true
        });

        // Auto hide dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('.dropdown-menu.show');
            const inputs = document.querySelectorAll('input[wire\\:model*="search"]');

            let clickedOnInput = false;
            let clickedOnDropdown = false;

            // Check if clicked on input
            inputs.forEach(function(input) {
                if (input.contains(event.target) || input === event.target) {
                    clickedOnInput = true;
                }
            });

            // Check if clicked on dropdown
            dropdowns.forEach(function(dropdown) {
                if (dropdown.contains(event.target)) {
                    clickedOnDropdown = true;
                }
            });

            // Hide dropdowns if clicked outside
            if (!clickedOnInput && !clickedOnDropdown) {
                @this.call('hideDropdowns');
            }
        });

        // Hide dropdowns on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                @this.call('hideDropdowns');
            }
        });
    </script>
@endpush
