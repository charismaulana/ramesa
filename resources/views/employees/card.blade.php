@extends('layouts.app')

@section('content')
    <div class="page-header d-flex justify-between align-items-center">
        <div>
            <h1 class="page-title">MEAL CARD</h1>
            <p class="page-subtitle">{{ $employee->name }}</p>
        </div>
        <div class="d-flex gap-1">
            <button onclick="downloadCard()" class="btn btn-primary" id="download-btn">
                <i class="bi bi-download"></i>
                Download PNG
            </button>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Back
            </a>
        </div>
    </div>

    <div class="row" style="justify-content: center;">
        <div class="col-4">
            <div class="meal-card" id="meal-card">
                <div class="meal-card-wrapper" id="card-wrapper">
                    <div class="meal-card-inner">
                        <div class="meal-card-glow"></div>

                        <div class="meal-card-header">
                            <div class="meal-card-logo">
                                @if(file_exists(public_path('images/logo.png')))
                                    <img src="{{ asset('images/logo.png') }}" alt="Logo">
                                @else
                                    <i class="bi bi-qr-code"></i>
                                @endif
                            </div>
                            <div class="meal-card-title">MEAL CARD</div>
                            <div class="meal-card-subtitle">Ramesa - Ramba Meal System</div>
                        </div>

                        <div class="meal-card-body">
                            <div class="meal-card-qr">
                                @if($employee->qr_code_path && Storage::disk('public')->exists($employee->qr_code_path))
                                    @php
                                        $qrPath = Storage::disk('public')->path($employee->qr_code_path);
                                        $svgContent = file_get_contents($qrPath);
                                        // Add width and height to SVG for proper rendering
                                        $svgContent = preg_replace('/<svg/', '<svg width="80" height="80"', $svgContent, 1);
                                    @endphp
                                    {!! $svgContent !!}
                                @else
                                    <div
                                        style="width: 80px; height: 80px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999; font-size: 0.5rem;">
                                        No QR
                                    </div>
                                @endif
                            </div>

                            <div class="meal-card-info">
                                <div class="meal-card-name">{{ $employee->name }}</div>
                                <div class="meal-card-id">{{ $employee->employee_number }}</div>
                                @if($employee->department)
                                    <div class="meal-card-dept">{{ $employee->department }}</div>
                                @endif
                                @if($employee->position)
                                    <div class="meal-card-position">{{ $employee->position }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="meal-card-footer">
                            <div class="meal-card-notes">
                                Pindai QR Code di Mess Hall sebelum ambil makan, terima kasih!
                            </div>
                            <div class="meal-card-status {{ $employee->active_status }}">
                                {{ strtoupper($employee->active_status) }}
                            </div>
                            <div class="meal-card-copyright">© GS Ramba 2025</div>
                        </div>

                        <div class="meal-card-decoration">
                            <div class="decoration-line"></div>
                            <div class="decoration-line"></div>
                            <div class="decoration-line"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .meal-card {
            perspective: 1000px;
            width: 53.98mm;
            margin: 0 auto;
        }

        .meal-card-wrapper {
            background: #0d0404;
            padding: 0;
        }

        .meal-card-inner {
            background: linear-gradient(145deg, #1a0808 0%, #0d0404 50%, #1a0a0a 100%);
            border-radius: 8px;
            padding: 8mm 5mm;
            width: 53.98mm;
            height: 85.60mm;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            box-shadow:
                0 10px 40px rgba(0, 0, 0, 0.5),
                0 0 60px rgba(255, 69, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .meal-card-glow {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 30%, rgba(255, 69, 0, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .meal-card-header {
            text-align: center;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .meal-card-logo {
            width: 32px;
            height: 32px;
            margin: 0 auto 0.3rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 15px rgba(255, 69, 0, 0.5);
        }

        .meal-card-logo img {
            width: 22px;
            height: 22px;
            object-fit: contain;
        }

        .meal-card-logo i {
            font-size: 1rem;
            color: white;
        }

        .meal-card-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 2px;
        }

        .meal-card-subtitle {
            font-size: 0.5rem;
            color: var(--text-muted);
            letter-spacing: 1px;
            margin-top: 0.1rem;
        }

        .meal-card-body {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .meal-card-qr {
            display: inline-block;
            padding: 0.4rem;
            background: white;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }

        .meal-card-qr img {
            width: 80px;
            height: 80px;
            display: block;
        }

        .meal-card-info {
            margin-bottom: 0.2rem;
        }

        .meal-card-name {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.6rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.15rem;
        }

        .meal-card-id {
            font-family: 'Orbitron', monospace;
            font-size: 0.5rem;
            color: var(--accent);
            letter-spacing: 1px;
            margin-bottom: 0.15rem;
        }

        .meal-card-dept,
        .meal-card-position {
            font-size: 0.45rem;
            color: var(--text-secondary);
        }

        .meal-card-footer {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .meal-card-status {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 10px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.4rem;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .meal-card-status.active {
            background: rgba(0, 255, 136, 0.15);
            color: var(--success);
            border: 1px solid rgba(0, 255, 136, 0.3);
        }

        .meal-card-status.inactive {
            background: rgba(255, 68, 68, 0.15);
            color: var(--error);
            border: 1px solid rgba(255, 68, 68, 0.3);
        }

        .meal-card-copyright {
            font-size: 0.35rem;
            color: var(--text-muted);
            margin-top: 0.3rem;
            letter-spacing: 0.5px;
        }

        .meal-card-notes {
            font-size: 0.38rem;
            color: var(--accent);
            font-style: italic;
            text-align: center;
            margin-bottom: 0.3rem;
            padding: 0.2rem;
            background: rgba(255, 215, 0, 0.1);
            border-radius: 4px;
            border: 1px dashed rgba(255, 215, 0, 0.3);
            line-height: 1.3;
        }

        .meal-card-decoration {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            display: flex;
            gap: 2px;
            padding: 0 1rem;
        }

        .decoration-line {
            flex: 1;
            height: 100%;
            border-radius: 2px;
        }

        .decoration-line:nth-child(1) {
            background: var(--primary);
        }

        .decoration-line:nth-child(2) {
            background: var(--secondary);
        }

        .decoration-line:nth-child(3) {
            background: var(--accent);
        }

        @media print {

            .navbar,
            .page-header,
            .btn {
                display: none !important;
            }

            body {
                background: white !important;
            }

            body::before {
                display: none !important;
            }

            .main-content {
                padding: 0 !important;
            }

            .meal-card-inner {
                box-shadow: none !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
        function downloadCard() {
            const btn = document.getElementById('download-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
            btn.disabled = true;

            const element = document.querySelector('.meal-card-inner');

            html2canvas(element, {
                backgroundColor: '#0d0404',
                scale: 2,
                useCORS: true,
                allowTaint: true,
                logging: false,
                scrollX: 0,
                scrollY: -window.scrollY,
                windowWidth: element.scrollWidth,
                windowHeight: element.scrollHeight
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = '{{ str_replace(" ", "_", $employee->name) }}_{{ str_replace(" ", "_", $employee->department ?? "NoDept") }}_{{ str_replace(" ", "_", $employee->position ?? "NoPos") }}_{{ $employee->employee_number }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(err => {
                console.error('Error generating image:', err);
                alert('Failed to generate card. Please try the Print option instead.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
@endpush