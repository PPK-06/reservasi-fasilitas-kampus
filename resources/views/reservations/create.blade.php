@extends('layouts.app')

@section('title', 'Ajukan Reservasi')
@section('page-title', 'Ajukan Reservasi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('reservations.store') }}">
                    @csrf

                    {{-- Fasilitas --}}
                    <div class="mb-3">
                        <label for="facility_id" class="form-label fw-semibold">Fasilitas</label>
                        <select name="facility_id" id="facility_id"
                                class="form-select @error('facility_id') is-invalid @enderror" required>
                            <option value="">-- Pilih fasilitas --</option>
                            @foreach ($facilities as $facility)
                                <option value="{{ $facility->id }}"
                                    {{ old('facility_id', $selectedFacility?->id) == $facility->id ? 'selected' : '' }}>
                                    {{ $facility->name }} ({{ $facility->type }} — {{ $facility->location }})
                                </option>
                            @endforeach
                        </select>
                        @error('facility_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tanggal --}}
                    <div class="mb-3">
                        <label for="date" class="form-label fw-semibold">Tanggal</label>
                        <input type="date" name="date" id="date"
                               class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date', request('date')) }}"
                               min="{{ now()->addDay()->toDateString() }}"
                               max="{{ now()->addDays(30)->toDateString() }}"
                               required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Minimal besok, maksimal 30 hari ke depan.</div>
                    </div>

                    {{-- Slot Waktu — Pemilihan Langsung di Grid --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">
                                <i class="bi bi-grid-3x3-gap me-1"></i>Pilih Slot Waktu (Klik Langsung di Grid)
                            </label>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-muted" id="btn-reset-slots">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                            </button>
                        </div>

                        <div class="alert alert-light border d-flex align-items-center justify-content-between py-2 px-3 mb-2" id="slot-selection-info">
                            <div class="small">
                                <i class="bi bi-info-circle me-1 text-primary"></i>
                                <span id="slot-info-text">Pilih fasilitas dan tanggal terlebih dahulu, lalu klik slot jam mulai dan jam selesai.</span>
                            </div>
                            <span class="badge bg-primary d-none" id="slot-badge-count">0 Slot</span>
                        </div>

                        <div class="row row-cols-3 row-cols-sm-4 row-cols-md-6 g-2 mb-2" id="slot-grid-container">
                            @foreach ($startSlots as $index => $slot)
                                <div class="col">
                                    <button type="button"
                                            class="btn btn-outline-secondary w-100 py-2 slot-btn position-relative"
                                            data-index="{{ $index }}"
                                            data-start="{{ $slot }}"
                                            data-end="{{ $endSlots[$index] }}"
                                            style="font-size: 0.83rem; font-weight: 500;">
                                        <div class="slot-time">{{ $slot }}</div>
                                        <small class="slot-status-label d-block text-muted" style="font-size: 0.65rem;">Tersedia</small>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        {{-- Hidden inputs --}}
                        <input type="hidden" name="start_slot" id="start_slot" value="{{ old('start_slot', request('start_slot')) }}" required>
                        <input type="hidden" name="end_slot" id="end_slot" value="{{ old('end_slot', request('end_slot')) }}" required>

                        @error('start_slot')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('end_slot')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        {{-- Dropdown Sinkronisasi / Manual Fallback --}}
                        <details class="mt-2 text-muted" style="font-size: 0.8rem;">
                            <summary class="cursor-pointer">Pilihan manual via dropdown</summary>
                            <div class="row g-2 mt-1">
                                <div class="col-6">
                                    <label for="select_start_slot" class="form-label small mb-1">Jam Mulai</label>
                                    <select id="select_start_slot" class="form-select form-select-sm">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($startSlots as $slot)
                                            <option value="{{ $slot }}" {{ old('start_slot', request('start_slot')) === $slot ? 'selected' : '' }}>
                                                {{ $slot }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="select_end_slot" class="form-label small mb-1">Jam Selesai</label>
                                    <select id="select_end_slot" class="form-select form-select-sm">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($endSlots as $slot)
                                            <option value="{{ $slot }}" {{ old('end_slot', request('end_slot')) === $slot ? 'selected' : '' }}>
                                                {{ $slot }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </details>
                    </div>

                    {{-- Tujuan --}}
                    <div class="mb-4">
                        <label for="purpose" class="form-label fw-semibold">Tujuan Penggunaan</label>
                        <input type="text" name="purpose" id="purpose"
                               class="form-control @error('purpose') is-invalid @enderror"
                               value="{{ old('purpose') }}"
                               minlength="5" maxlength="255" required>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-1"></i>Ajukan
                        </button>
                        <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const startTimes = @json($startSlots);
    const endTimes = @json($endSlots);

    const facilitySelect = document.getElementById('facility_id');
    const dateInput = document.getElementById('date');
    const hiddenStart = document.getElementById('start_slot');
    const hiddenEnd = document.getElementById('end_slot');
    const selectStart = document.getElementById('select_start_slot');
    const selectEnd = document.getElementById('select_end_slot');
    const infoText = document.getElementById('slot-info-text');
    const badgeCount = document.getElementById('slot-badge-count');
    const btnReset = document.getElementById('btn-reset-slots');
    const slotButtons = document.querySelectorAll('.slot-btn');

    let startIndex = null;
    let endIndex = null;
    let availabilityData = {};

    function updateHighlight() {
        slotButtons.forEach((btn, idx) => {
            const isBooked = btn.dataset.status === 'booked';
            const isPastLimit = btn.dataset.status === 'past_limit';

            btn.classList.remove('btn-primary', 'text-white', 'btn-outline-primary', 'btn-outline-secondary', 'btn-danger', 'btn-secondary');

            if (isBooked) {
                btn.classList.add('btn-danger');
                btn.disabled = true;
                btn.querySelector('.slot-status-label').textContent = 'Terisi';
            } else if (isPastLimit) {
                btn.classList.add('btn-secondary');
                btn.disabled = true;
                btn.querySelector('.slot-status-label').textContent = 'Lewat';
            } else {
                btn.disabled = false;
                if (startIndex !== null && endIndex !== null && idx >= startIndex && idx <= endIndex) {
                    btn.classList.add('btn-primary', 'text-white');
                    btn.querySelector('.slot-status-label').textContent = idx === startIndex ? 'Mulai' : (idx === endIndex ? 'Selesai' : 'Dipilih');
                } else if (startIndex !== null && endIndex === null && idx === startIndex) {
                    btn.classList.add('btn-primary', 'text-white');
                    btn.querySelector('.slot-status-label').textContent = 'Mulai';
                } else {
                    btn.classList.add('btn-outline-secondary');
                    btn.querySelector('.slot-status-label').textContent = 'Tersedia';
                }
            }
        });

        if (startIndex !== null && endIndex !== null) {
            const count = (endIndex - startIndex) + 1;
            const sTime = startTimes[startIndex];
            const eTime = endTimes[endIndex];
            hiddenStart.value = sTime;
            hiddenEnd.value = eTime;
            if (selectStart) selectStart.value = sTime;
            if (selectEnd) selectEnd.value = eTime;

            infoText.innerHTML = `Terpilih: <strong>${sTime}</strong> – <strong>${eTime}</strong>`;
            badgeCount.textContent = `${count} Slot (${(count * 0.5)} Jam)`;
            badgeCount.classList.remove('d-none');
        } else if (startIndex !== null) {
            const sTime = startTimes[startIndex];
            hiddenStart.value = sTime;
            hiddenEnd.value = '';
            if (selectStart) selectStart.value = sTime;
            infoText.innerHTML = `Mulai: <strong>${sTime}</strong>. Klik slot selesai berikutnya.`;
            badgeCount.classList.add('d-none');
        } else {
            hiddenStart.value = '';
            hiddenEnd.value = '';
            if (selectStart) selectStart.value = '';
            if (selectEnd) selectEnd.value = '';
            infoText.textContent = 'Klik slot jam mulai lalu jam selesai di grid.';
            badgeCount.classList.add('d-none');
        }
    }

    function checkSlotRangeValidity(startIdx, endIdx) {
        for (let i = startIdx; i <= endIdx; i++) {
            const btn = document.querySelector(`.slot-btn[data-index="${i}"]`);
            if (btn && btn.dataset.status === 'booked') {
                return false;
            }
        }
        return true;
    }

    slotButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            if (this.disabled) return;
            const clickedIdx = parseInt(this.dataset.index, 10);

            if (startIndex === null || (startIndex !== null && endIndex !== null)) {
                startIndex = clickedIdx;
                endIndex = clickedIdx;
            } else if (startIndex !== null && endIndex === null) {
                if (clickedIdx >= startIndex) {
                    if (checkSlotRangeValidity(startIndex, clickedIdx)) {
                        endIndex = clickedIdx;
                    } else {
                        alert('Rentang slot melewati jadwal yang sudah terisi.');
                        return;
                    }
                } else {
                    startIndex = clickedIdx;
                    endIndex = clickedIdx;
                }
            }
            updateHighlight();
        });
    });

    btnReset?.addEventListener('click', function () {
        startIndex = null;
        endIndex = null;
        updateHighlight();
    });

    selectStart?.addEventListener('change', function () {
        const val = this.value;
        const idx = startTimes.indexOf(val);
        if (idx !== -1) {
            startIndex = idx;
            if (endIndex === null || endIndex < idx) {
                endIndex = idx;
            }
            updateHighlight();
        }
    });

    selectEnd?.addEventListener('change', function () {
        const val = this.value;
        const idx = endTimes.indexOf(val);
        if (idx !== -1) {
            endIndex = idx;
            if (startIndex === null || startIndex > idx) {
                startIndex = idx;
            }
            updateHighlight();
        }
    });

    function loadAvailability() {
        const fId = facilitySelect.value;
        const dt = dateInput.value;
        if (!fId || !dt) return;

        fetch(`{{ route('reservations.availability') }}?facility_id=${fId}&date=${dt}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.slots) {
                availabilityData = data.slots;
                slotButtons.forEach(btn => {
                    const sTime = btn.dataset.start;
                    const slotInfo = data.slots[sTime];
                    if (slotInfo) {
                        btn.dataset.status = slotInfo.status;
                    }
                });
                updateHighlight();
            }
        })
        .catch(() => {});
    }

    facilitySelect?.addEventListener('change', loadAvailability);
    dateInput?.addEventListener('change', loadAvailability);

    if (hiddenStart.value) {
        const sIdx = startTimes.indexOf(hiddenStart.value);
        if (sIdx !== -1) startIndex = sIdx;
    }
    if (hiddenEnd.value) {
        const eIdx = endTimes.indexOf(hiddenEnd.value);
        if (eIdx !== -1) endIndex = eIdx;
    }

    if (facilitySelect?.value && dateInput?.value) {
        loadAvailability();
    } else {
        updateHighlight();
    }
});
</script>
@endpush
