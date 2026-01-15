@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ __('messages.export_data') }}</h1>
        <p class="page-subtitle">{{ __('messages.configure_export') }}</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('messages.export_options') }}</h2>
        </div>

        <form action="{{ route('historical.export') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.start_date') }} *</label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-01') }}" required>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.end_date') }} *</label>
                        <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.location') }}</label>
                        <select name="location" class="form-control">
                            <option value="">{{ __('messages.all_locations') }}</option>
                            @foreach($locations as $location)
                                <option value="{{ $location }}">{{ $location }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.meal_type') }}</label>
                        <select name="meal_type" class="form-control">
                            <option value="">{{ __('messages.all_meal_types') }}</option>
                            @foreach($mealTypes as $meal)
                                <option value="{{ $meal }}">{{ ucfirst($meal) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('messages.export_type') }}</label>
                <div class="export-type-options">
                    <label class="export-type-option">
                        <input type="radio" name="export_type" value="detailed" checked>
                        <div class="export-type-card">
                            <div class="export-type-icon"><i class="bi bi-list-ul"></i></div>
                            <div class="export-type-info">
                                <strong>{{ __('messages.detailed') }}</strong>
                                <p>{{ __('messages.detailed_desc') }}</p>
                            </div>
                        </div>
                    </label>
                    <label class="export-type-option">
                        <input type="radio" name="export_type" value="summary">
                        <div class="export-type-card">
                            <div class="export-type-icon"><i class="bi bi-bar-chart"></i></div>
                            <div class="export-type-info">
                                <strong>{{ __('messages.summary') }}</strong>
                                <p>{{ __('messages.summary_desc') }}</p>
                            </div>
                        </div>
                    </label>
                    <label class="export-type-option">
                        <input type="radio" name="export_type" value="recap">
                        <div class="export-type-card">
                            <div class="export-type-icon"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                            <div class="export-type-info">
                                <strong>{{ __('messages.recap_export') }}</strong>
                                <p>{{ __('messages.recap_desc') }}</p>
                            </div>
                        </div>
                    </label>
                    <label class="export-type-option">
                        <input type="radio" name="export_type" value="daily">
                        <div class="export-type-card">
                            <div class="export-type-icon"><i class="bi bi-calendar-check"></i></div>
                            <div class="export-type-info">
                                <strong>{{ __('messages.daily') }}</strong>
                                <p>{{ __('messages.daily_desc') }}</p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Recap Export Fields (shown only when Recap is selected) -->
            <div id="recapFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.company_header') }}</label>
                    <input type="text" name="company_header" class="form-control" value="PT. Brylian Indah"
                        placeholder="Company name...">
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.prepared_by') }}</label>
                            <input type="text" name="prepared_by" class="form-control" placeholder="{{ __('messages.name') }}...">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.position') }}</label>
                            <input type="text" name="prepared_position" class="form-control" value="Camp Boss"
                                placeholder="{{ __('messages.position') }}...">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.checked_by') }}</label>
                            <input type="text" name="checked_by" class="form-control" value="Dedy B. / Rai A. / Marnita"
                                placeholder="{{ __('messages.name') }}...">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.position') }}</label>
                            <input type="text" name="checked_position" class="form-control" value="GS Ramba"
                                placeholder="{{ __('messages.position') }}...">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.logo') }} <span class="text-danger">*</span></label>

                    @if(count($savedLogos) > 0)
                        <div class="logo-gallery mb-2">
                            @foreach($savedLogos as $index => $logo)
                                <label class="logo-option">
                                    <input type="radio" name="selected_logo" value="{{ $logo['path'] }}" {{ $index === 0 ? 'checked' : '' }} required>
                                    <div class="logo-preview">
                                        <img src="{{ $logo['url'] }}" alt="{{ $logo['filename'] }}">
                                        <span class="logo-name">{{ $logo['filename'] }}</span>
                                    </div>
                                </label>
                            @endforeach
                            <label class="logo-option">
                                <input type="radio" name="selected_logo" value="new" id="newLogoRadio">
                                <div class="logo-preview upload-new">
                                    <i class="bi bi-plus-circle"></i>
                                    <span class="logo-name">{{ __('messages.upload_new') }}</span>
                                </div>
                            </label>
                        </div>
                    @endif

                    <div id="newLogoUpload" style="{{ count($savedLogos) > 0 ? 'display: none;' : '' }}">
                        <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/jpg"
                            id="logoFileInput" {{ count($savedLogos) === 0 ? 'required' : '' }}>
                        <small class="text-muted">{{ __('messages.upload_logo_hint') }}</small>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-1 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-download"></i> {{ __('messages.export_excel') }}
                </button>
                <a href="{{ route('historical.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> {{ __('messages.back_to_historical') }}
                </a>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        .export-type-options {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .export-type-option {
            flex: 1;
            min-width: 250px;
            cursor: pointer;
        }

        .export-type-option input {
            display: none;
        }

        .export-type-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid var(--card-border);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .export-type-option input:checked+.export-type-card {
            border-color: var(--primary);
            background: rgba(255, 69, 0, 0.1);
        }

        .export-type-card:hover {
            border-color: var(--primary-light);
        }

        .export-type-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .export-type-info strong {
            display: block;
            margin-bottom: 0.25rem;
        }

        .export-type-info p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin: 0;
        }

        /* Responsive button styles */
        .d-flex.gap-1.mt-3 {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .d-flex.gap-1.mt-3 {
                flex-direction: column;
            }

            .d-flex.gap-1.mt-3 .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        /* Logo gallery styles */
        .logo-gallery {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .logo-option {
            cursor: pointer;
        }

        .logo-option input {
            display: none;
        }

        .logo-preview {
            width: 100px;
            height: 100px;
            border: 2px solid var(--card-border);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.03);
        }

        .logo-option input:checked+.logo-preview {
            border-color: var(--primary);
            background: rgba(255, 69, 0, 0.1);
        }

        .logo-preview:hover {
            border-color: var(--primary-light);
        }

        .logo-preview img {
            max-width: 70px;
            max-height: 60px;
            object-fit: contain;
        }

        .logo-preview.upload-new {
            border-style: dashed;
        }

        .logo-preview.upload-new i {
            font-size: 2rem;
            color: var(--text-muted);
        }

        .logo-name {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-align: center;
            margin-top: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 90px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Show/hide recap fields based on export type selection
        document.querySelectorAll('input[name="export_type"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const recapFields = document.getElementById('recapFields');
                const pdfBtn = document.getElementById('pdfExportBtn');

                if (this.value === 'recap' || this.value === 'daily') {
                    recapFields.style.display = 'block';
                    pdfBtn.style.display = this.value === 'recap' ? 'inline-block' : 'none';
                } else {
                    recapFields.style.display = 'none';
                    pdfBtn.style.display = 'none';
                }
            });
        });

        function exportPDF() {
            const params = new URLSearchParams({
                start_date: document.querySelector('input[name="start_date"]').value,
                end_date: document.querySelector('input[name="end_date"]').value,
                location: document.querySelector('select[name="location"]').value || '',
                company_header: document.querySelector('input[name="company_header"]').value,
                prepared_by: document.querySelector('input[name="prepared_by"]').value,
                prepared_position: document.querySelector('input[name="prepared_position"]').value,
                checked_by: document.querySelector('input[name="checked_by"]').value,
                checked_position: document.querySelector('input[name="checked_position"]').value
            });
            window.location.href = '{{ route("historical.recap-pdf") }}?' + params.toString();
        }

        // Toggle new logo upload field
        document.querySelectorAll('input[name="selected_logo"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const uploadDiv = document.getElementById('newLogoUpload');
                const fileInput = document.getElementById('logoFileInput');
                if (this.value === 'new') {
                    uploadDiv.style.display = 'block';
                    fileInput.required = true;
                } else {
                    uploadDiv.style.display = 'none';
                    fileInput.required = false;
                }
            });
        });
    </script>
@endpush