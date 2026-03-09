<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="confirmSubmit">

                        {{-- Hidden auto-fill fields (tidak tampil di UI, tapi data terkirim) --}}
                        <input type="hidden" wire:model="req_dept_id">
                        <input type="hidden" wire:model="req_user_id">

                        <div class="row">

                            {{-- Notification Description --}}
                            <div class="col-md-12 mb-3">
                                <label class="form-label mb-2">* Notification Description</label>
                                <textarea wire:model="work_desc" rows="3" placeholder="Deskripsi notifikasi (wajib diisi)"
                                    class="form-control @error('work_desc') is-invalid @enderror"></textarea>
                                @error('work_desc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Equipment Search --}}
                            <div class="col-md-12 mb-3">
                                <label class="form-label mb-2">* Equipment</label>
                                <div class="position-relative" x-data @click.outside="$wire.hideDropdowns()">
                                    <div class="input-group">
                                        <input type="text" wire:model.live.debounce.300ms="equipment_search"
                                            wire:click="$set('showEquipmentDropdown', true)"
                                            placeholder="Klik atau ketik untuk mencari equipment..."
                                            class="form-control @error('equipment_id') is-invalid @enderror"
                                            autocomplete="off" />
                                        @if ($equipment_id)
                                            <div class="input-group-append">
                                                <button type="button" wire:click="resetEquipment"
                                                    class="btn btn-outline-secondary">
                                                    <i class="fas fa-times"></i> Ganti
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    @if (!$equipment_id)
                                        <small class="text-muted">Ketik nama equipment untuk mencari dari daftar</small>
                                    @endif

                                    @error('equipment_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror

                                    {{-- Dropdown hasil pencarian --}}
                                    @if ($showEquipmentDropdown)
                                        <div class="position-absolute w-100 bg-white border rounded shadow-sm mt-1"
                                            style="z-index: 1050; max-height: 250px; overflow-y: auto;">
                                            @forelse($equipments as $eq)
                                                <button type="button" wire:click="selectEquipment({{ $eq->id }})"
                                                    class="d-block w-100 text-left btn btn-light border-bottom px-3 py-2"
                                                    style="border-radius: 0;">
                                                    {{ $eq->name }}
                                                </button>
                                            @empty
                                                <div class="px-3 py-2 text-muted small text-center">
                                                    Equipment tidak ditemukan
                                                </div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Auto-fill: Functional Location, Resource, Plant --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2 text-muted">Functional Location</label>
                                <input type="text" value="{{ $func_loc_name ?? '-' }}" readonly
                                    class="form-control bg-light text-muted" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2 text-muted">Resource</label>
                                <input type="text" value="{{ $resource_name ?? '-' }}" readonly
                                    class="form-control bg-light text-muted" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2 text-muted">Plant</label>
                                <input type="text" value="{{ $plant_name ?? '-' }}" readonly
                                    class="form-control bg-light text-muted" />
                            </div>

                            {{-- Planner Group --}}
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

                            {{-- Malfunction Start --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Malfunction Start</label>
                                <input type="datetime-local" wire:model.blur="malfunction_start"
                                    class="form-control @error('malfunction_start') is-invalid @enderror">
                                @error('malfunction_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Notification Date --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2">* Notification Date</label>
                                <input type="datetime-local" wire:model.blur="notification_date"
                                    class="form-control @error('notification_date') is-invalid @enderror">
                                @error('notification_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Priority --}}
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

                            {{-- Urgent Level --}}
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

                            {{-- Breakdown --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label mb-2 d-block">Breakdown</label>
                                <label class="switch mb-0">
                                    <input type="checkbox" wire:model.live="breakdown">
                                    <span class="slider">
                                        <span class="text yes">YES</span>
                                        <span class="text no">NO</span>
                                    </span>
                                </label>
                            </div>

                            {{-- Notes (opsional) --}}
                            <div class="col-md-12 mb-3">
                                <label class="form-label mb-2">Notes <span
                                        class="text-muted">(opsional)</span></label>
                                <textarea wire:model.blur="notes" rows="3" placeholder="Catatan tambahan (opsional)"
                                    class="form-control @error('notes') is-invalid @enderror"></textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>{{-- end .row --}}

                        {{-- Submit Buttons --}}
                        <div class="d-flex justify-content-end mt-3">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-pill mr-2">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-pill" wire:loading.attr="disabled">
                                <span wire:loading wire:target="confirmSubmit"
                                    class="spinner-border spinner-border-sm mr-1"></span>
                                <i wire:loading.remove wire:target="confirmSubmit" class="fas fa-save mr-1"></i>
                                Submit
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Overlay untuk close dropdown saat klik luar --}}
    @if ($showEquipmentDropdown)
        <div wire:click="hideDropdowns" class="position-fixed"
            style="top:0; left:0; width:100%; height:100%; z-index:1040;">
        </div>
    @endif

    {{-- Loading Overlay --}}
    <div wire:loading.delay wire:target="submit" class="position-fixed"
        style="top:0; left:0; width:100%; height:100%; background:rgba(178,188,202,0.3); z-index:9999;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="text-center">
                <div class="mb-3"
                    style="width:3rem; height:3rem; margin:0 auto; border-radius:50%; background:#1572e8; animation:grow 1.5s ease-in-out infinite;">
                </div>
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
            color: white;
            opacity: 1;
        }

        .yes {
            opacity: 0;
        }

        .no {
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
        document.addEventListener('livewire:initialized', function() {

            Livewire.on('show-success-alert', function(data) {
                swal({
                    title: data[0].title,
                    text: data[0].message,
                    icon: "success",
                    button: "OK",
                }).then((result) => {
                    if (result) window.location.href = data[0].redirect;
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

            Livewire.on('confirmSubmit', function() {
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
                    if (result) @this.submit();
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
                    if (result) window.location.href = data[0].redirect;
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

            Livewire.on('confirmSubmit', function() {
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
                    if (result) @this.submit();
                });
            });

        }, {
            once: true
        });

        // Escape key menutup dropdown
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                @this.call('hideDropdowns');
            }
        });
    </script>
@endpush
