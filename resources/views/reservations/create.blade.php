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
                               value="{{ old('date') }}"
                               min="{{ now()->addDay()->toDateString() }}"
                               max="{{ now()->addDays(30)->toDateString() }}"
                               required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Minimal besok, maksimal 30 hari ke depan.</div>
                    </div>

                    {{-- Jam Mulai & Selesai --}}
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="start_slot" class="form-label fw-semibold">Jam Mulai</label>
                            <select name="start_slot" id="start_slot"
                                    class="form-select @error('start_slot') is-invalid @enderror" required>
                                <option value="">-- Pilih --</option>
                                @foreach ($startSlots as $slot)
                                    <option value="{{ $slot }}" {{ old('start_slot') === $slot ? 'selected' : '' }}>
                                        {{ $slot }}
                                    </option>
                                @endforeach
                            </select>
                            @error('start_slot')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label for="end_slot" class="form-label fw-semibold">Jam Selesai</label>
                            <select name="end_slot" id="end_slot"
                                    class="form-select @error('end_slot') is-invalid @enderror" required>
                                <option value="">-- Pilih --</option>
                                @foreach ($endSlots as $slot)
                                    <option value="{{ $slot }}" {{ old('end_slot') === $slot ? 'selected' : '' }}>
                                        {{ $slot }}
                                    </option>
                                @endforeach
                            </select>
                            @error('end_slot')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
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
