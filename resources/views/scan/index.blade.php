@extends('layouts.app')

@section('content')
    <div class="page-header text-center">
        <h1 class="page-title">QR SCAN STATION</h1>
        <p class="page-subtitle">Scan employee QR code for meal attendance</p>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">QR SCAN STATION</h2>
                </div>

                <div style="padding: 1.5rem;">
                    <!-- Meal Type & Location in One Row -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 1.5rem;">
                        <!-- Meal Type Selection -->
                        <div>
                            <label class="form-label">Meal Type</label>
                            <div
                                style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-top: 0.75rem;">
                                <button type="button" class="meal-btn active" data-meal="breakfast" style="
                                                    padding: 1rem;
                                                    border-radius: 12px;
                                                    border: 2px solid var(--card-border);
                                                    background: var(--card-bg);
                                                    color: var(--text-primary);
                                                    cursor: pointer;
                                                    transition: all 0.3s ease;
                                                    font-family: 'Rajdhani', sans-serif;
                                                    font-size: 0.95rem;
                                                    font-weight: 600;
                                                ">
                                    <i class="bi bi-sunrise"
                                        style="font-size: 1.5rem; display: block; margin-bottom: 0.25rem; color: var(--accent);"></i>
                                    Breakfast
                                </button>
                                <button type="button" class="meal-btn" data-meal="lunch" style="
                                                    padding: 1rem;
                                                    border-radius: 12px;
                                                    border: 2px solid var(--card-border);
                                                    background: var(--card-bg);
                                                    color: var(--text-primary);
                                                    cursor: pointer;
                                                    transition: all 0.3s ease;
                                                    font-family: 'Rajdhani', sans-serif;
                                                    font-size: 0.95rem;
                                                    font-weight: 600;
                                                ">
                                    <i class="bi bi-sun"
                                        style="font-size: 1.5rem; display: block; margin-bottom: 0.25rem; color: var(--secondary);"></i>
                                    Lunch
                                </button>
                                <button type="button" class="meal-btn" data-meal="dinner" style="
                                                    padding: 1rem;
                                                    border-radius: 12px;
                                                    border: 2px solid var(--card-border);
                                                    background: var(--card-bg);
                                                    color: var(--text-primary);
                                                    cursor: pointer;
                                                    transition: all 0.3s ease;
                                                    font-family: 'Rajdhani', sans-serif;
                                                    font-size: 0.95rem;
                                                    font-weight: 600;
                                                ">
                                    <i class="bi bi-sunset"
                                        style="font-size: 1.5rem; display: block; margin-bottom: 0.25rem; color: var(--primary);"></i>
                                    Dinner
                                </button>
                                <button type="button" class="meal-btn" data-meal="supper" style="
                                                    padding: 1rem;
                                                    border-radius: 12px;
                                                    border: 2px solid var(--card-border);
                                                    background: var(--card-bg);
                                                    color: var(--text-primary);
                                                    cursor: pointer;
                                                    transition: all 0.3s ease;
                                                    font-family: 'Rajdhani', sans-serif;
                                                    font-size: 0.95rem;
                                                    font-weight: 600;
                                                ">
                                    <i class="bi bi-moon-stars"
                                        style="font-size: 1.5rem; display: block; margin-bottom: 0.25rem; color: var(--primary-light);"></i>
                                    Supper
                                </button>
                            </div>
                        </div>

                        <!-- Location Selection -->
                        <div>
                            <label class="form-label">Meal Location</label>
                            <div
                                style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-top: 0.75rem;">
                                <button type="button" class="location-btn active" data-location="Ramba" style="
                                                    padding: 1rem;
                                                    border-radius: 12px;
                                                    border: 2px solid var(--card-border);
                                                    background: var(--card-bg);
                                                    color: var(--text-primary);
                                                    cursor: pointer;
                                                    transition: all 0.3s ease;
                                                    font-family: 'Rajdhani', sans-serif;
                                                    font-size: 0.95rem;
                                                    font-weight: 600;
                                                ">
                                    <i class="bi bi-geo-alt"
                                        style="font-size: 1.5rem; display: block; margin-bottom: 0.25rem; color: var(--primary);"></i>
                                    Ramba
                                </button>
                                <button type="button" class="location-btn" data-location="Bentayan" style="
                                                    padding: 1rem;
                                                    border-radius: 12px;
                                                    border: 2px solid var(--card-border);
                                                    background: var(--card-bg);
                                                    color: var(--text-primary);
                                                    cursor: pointer;
                                                    transition: all 0.3s ease;
                                                    font-family: 'Rajdhani', sans-serif;
                                                    font-size: 0.95rem;
                                                    font-weight: 600;
                                                ">
                                    <i class="bi bi-geo-alt"
                                        style="font-size: 1.5rem; display: block; margin-bottom: 0.25rem; color: var(--primary);"></i>
                                    Bentayan
                                </button>
                                <button type="button" class="location-btn" data-location="Mangunjaya" style="
                                                    padding: 1rem;
                                                    border-radius: 12px;
                                                    border: 2px solid var(--card-border);
                                                    background: var(--card-bg);
                                                    color: var(--text-primary);
                                                    cursor: pointer;
                                                    transition: all 0.3s ease;
                                                    font-family: 'Rajdhani', sans-serif;
                                                    font-size: 0.95rem;
                                                    font-weight: 600;
                                                ">
                                    <i class="bi bi-geo-alt"
                                        style="font-size: 1.5rem; display: block; margin-bottom: 0.25rem; color: var(--primary);"></i>
                                    Mangunjaya
                                </button>
                                <button type="button" class="location-btn" data-location="Keluang" style="
                                                    padding: 1rem;
                                                    border-radius: 12px;
                                                    border: 2px solid var(--card-border);
                                                    background: var(--card-bg);
                                                    color: var(--text-primary);
                                                    cursor: pointer;
                                                    transition: all 0.3s ease;
                                                    font-family: 'Rajdhani', sans-serif;
                                                    font-size: 0.95rem;
                                                    font-weight: 600;
                                                ">
                                    <i class="bi bi-geo-alt"
                                        style="font-size: 1.5rem; display: block; margin-bottom: 0.25rem; color: var(--primary);"></i>
                                    Keluang
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Input -->
                    <div class="form-group">
                        <label class="form-label">Scan QR Code Here</label>
                        <input type="text" id="qr-input" class="form-control"
                            placeholder="QR scanner input will appear here..."
                            style="font-size: 1.5rem; padding: 1.25rem; text-align: center; font-family: 'Orbitron', monospace;"
                            autofocus>
                        <p style="color: var(--text-muted); margin-top: 0.5rem; text-align: center;">
                            <i class="bi bi-info-circle"></i>
                            Position cursor here and scan employee QR code with physical scanner
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Display -->
    <div id="result-display" style="display: none; margin-top: 2rem;">
        <div class="card" id="result-card" style="text-align: center; padding: 3rem;">
            <div id="result-icon" style="font-size: 5rem; margin-bottom: 1rem;"></div>
            <h2 id="result-name" style="font-family: 'Orbitron', sans-serif; font-size: 2.5rem; margin-bottom: 0.5rem;">
            </h2>
            <p id="result-message" style="font-size: 1.5rem; margin-bottom: 0;"></p>
        </div>
    </div>

    <!-- Audio for thank you sound -->
    <audio id="success-sound" preload="auto">
        <source
            src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fZnGJkolyb2hxfomUkIVybWh0gYuQjH91cHJ6houMiH52c3V8hIqLh354dXd6gYeJh4J7eHl8gIWHhoN9ent9gIOFhIF+fX1+gIKDg4B/fn5/gIGCgYB/f39/gIGBgIB/f4CAgICAgH9/gICAgICAf4CAgICAgICAgICAgICAgICAgICAgICAgIB/gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgH+AgICAgICAgICAgICAgICAf4CAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIB/gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIB/"
            type="audio/wav">
    </audio>

@endsection

@push('styles')
    <style>
        .meal-btn.active {
            background: linear-gradient(135deg, rgba(255, 69, 0, 0.2), rgba(255, 140, 0, 0.1)) !important;
            border-color: var(--primary-light) !important;
            box-shadow: 0 0 25px rgba(255, 69, 0, 0.3);
        }

        .meal-btn:hover {
            background: rgba(255, 69, 0, 0.15);
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(255, 69, 0, 0.2);
        }

        .location-btn.active {
            background: linear-gradient(135deg, rgba(255, 69, 0, 0.2), rgba(255, 140, 0, 0.1)) !important;
            border-color: var(--primary-light) !important;
            box-shadow: 0 0 25px rgba(255, 69, 0, 0.3);
        }

        .location-btn:hover {
            background: rgba(255, 69, 0, 0.15);
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(255, 69, 0, 0.2);
        }

        #result-card.success {
            border-color: var(--success);
            box-shadow: 0 0 50px rgba(0, 255, 136, 0.3);
        }

        #result-card.error {
            border-color: var(--error);
            box-shadow: 0 0 50px rgba(255, 68, 68, 0.3);
        }

        #qr-input:focus {
            box-shadow: 0 0 30px rgba(255, 69, 0, 0.4);
        }

        @keyframes successPulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        #result-card.success {
            animation: successPulse 0.5s ease;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {

            /* Stack meal type and location sections */
            .card>div>div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
            }

            /* Make meal buttons 2 columns on mobile */
            .meal-btn {
                padding: 0.75rem !important;
                font-size: 0.85rem !important;
            }

            .meal-btn i {
                font-size: 1.2rem !important;
            }

            .location-btn {
                padding: 0.75rem !important;
                font-size: 0.85rem !important;
            }

            .location-btn i {
                font-size: 1.2rem !important;
            }

            /* QR input smaller on mobile */
            #qr-input {
                font-size: 1.2rem !important;
                padding: 1rem !important;
            }
        }

        @media (max-width: 480px) {

            /* Make meal/location buttons 2 columns on small screens */
            div[style*="grid-template-columns: repeat(4"] {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.5rem !important;
            }

            .meal-btn,
            .location-btn {
                padding: 0.6rem !important;
                font-size: 0.8rem !important;
            }

            .meal-btn i,
            .location-btn i {
                font-size: 1rem !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        let selectedMeal = 'breakfast';
        let selectedLocation = 'Ramba'; // Default location
        let processingTimeout = null;

        // Auto-select meal based on current time
        function getMealByTime() {
            const hour = new Date().getHours();
            // Breakfast: 05:00 - 09:00
            if (hour >= 5 && hour < 10) return 'breakfast';
            // Lunch: 11:00 - 15:00
            if (hour >= 11 && hour < 15) return 'lunch';
            // Dinner: 17:00 - 21:00
            if (hour >= 17 && hour < 21) return 'dinner';
            // Supper: 21:00 - 03:00 (spans midnight)
            if (hour >= 21 || hour < 3) return 'supper';
            // Default to lunch for other hours (09:00-11:00, 15:00-17:00, 03:00-05:00)
            return 'lunch';
        }

        // Initialize meal selection on page load
        document.addEventListener('DOMContentLoaded', function () {
            const autoMeal = getMealByTime();
            selectedMeal = autoMeal;

            // Update UI to show selected meal
            document.querySelectorAll('.meal-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.meal === autoMeal) {
                    btn.classList.add('active');
                }
            });
        });

        // Meal button selection
        document.querySelectorAll('.meal-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.meal-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedMeal = this.dataset.meal;
                document.getElementById('qr-input').focus();
            });
        });

        // Location button selection
        document.querySelectorAll('.location-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.location-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedLocation = this.dataset.location;
                document.getElementById('qr-input').focus();
            });
        });

        // QR Input handling
        const qrInput = document.getElementById('qr-input');

        qrInput.addEventListener('input', function () {
            clearTimeout(processingTimeout);

            // Process after 300ms of no input (QR scanners type fast)
            processingTimeout = setTimeout(() => {
                const value = this.value.trim();
                if (value) {
                    processQrCode(value);
                    this.value = '';
                }
            }, 300);
        });

        // Keep focus on input
        document.addEventListener('click', () => {
            qrInput.focus();
        });

        async function processQrCode(employeeNumber) {
            const resultDisplay = document.getElementById('result-display');
            const resultCard = document.getElementById('result-card');
            const resultIcon = document.getElementById('result-icon');
            const resultName = document.getElementById('result-name');
            const resultMessage = document.getElementById('result-message');

            try {
                const response = await fetch('{{ route("scan.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_number: employeeNumber,
                        meal_type: selectedMeal,
                        location: selectedLocation
                    })
                });

                const data = await response.json();

                resultDisplay.style.display = 'block';

                if (data.success) {
                    resultCard.className = 'card success';
                    resultIcon.innerHTML = '<i class="bi bi-check-circle" style="color: var(--success);"></i>';
                    resultName.textContent = data.employee_name;
                    resultName.style.color = 'var(--success)';
                    resultMessage.textContent = 'Thank you! Enjoy your ' + data.meal_type + '!';
                    resultMessage.style.color = 'var(--text-secondary)';

                    // Play success sound
                    playThankYouSound();
                } else {
                    resultCard.className = 'card error';
                    resultIcon.innerHTML = '<i class="bi bi-x-circle" style="color: var(--error);"></i>';
                    resultName.textContent = data.employee_name || 'Error';
                    resultName.style.color = 'var(--error)';
                    resultMessage.textContent = data.message;
                    resultMessage.style.color = 'var(--text-secondary)';

                    // Play error sound
                    playErrorSound();
                }

                // Hide result after 5 seconds
                setTimeout(() => {
                    resultDisplay.style.display = 'none';
                }, 5000);

            } catch (error) {
                resultDisplay.style.display = 'block';
                resultCard.className = 'card error';
                resultIcon.innerHTML = '<i class="bi bi-wifi-off" style="color: var(--error);"></i>';
                resultName.textContent = 'Connection Error';
                resultName.style.color = 'var(--error)';
                resultMessage.textContent = 'Please check your connection and try again.';
                resultMessage.style.color = 'var(--text-secondary)';

                // Play error sound
                playErrorSound();
            }
        }

        function playThankYouSound() {
            // Create speech synthesis for "Thank you"
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance('Terima kasih');
                utterance.lang = 'id-ID';
                utterance.rate = 0.9;
                utterance.pitch = 1;
                speechSynthesis.speak(utterance);
            }
        }

        function playErrorSound() {
            // Create speech synthesis for "Mohon maaf" (Sorry in Indonesian)
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance('Mohon maaf');
                utterance.lang = 'id-ID';
                utterance.rate = 0.9;
                utterance.pitch = 1;
                speechSynthesis.speak(utterance);
            }
        }
    </script>
@endpush