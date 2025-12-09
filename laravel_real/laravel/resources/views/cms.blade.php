@extends(backpack_view('blank'))

@php
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    try {
        $resp = Http::timeout(3)->get('http://homeassistant.local:8123/api/get_key')->json();
    } catch (\Exception $e) {
        $resp = null;
        Log::error('CMS View: Error fetching key - ' . $e->getMessage());
    }

    if ($resp !== null && isset($resp['key'])) {
        $mac_address = $resp['key'];
        Log::info('CMS View: MAC Address: ' . $mac_address);
        // $mac_address = 'DC:A6:32:3C:FB:DC';

        try {
            $resp = Http::withToken(config('cms.api_key'))
                ->get(config('cms.get_token'), ['mac_address' => $mac_address])
                ->json();
            Log::info('CMS View: Response from get_token: ' . json_encode($resp));

            if ($resp !== null && isset($resp['message'])) {
                if ($resp['message'] == 'Token not found.') {
                    $resp = Http::withToken(config('cms.api_key'))->get(config('cms.generate_token'))->json();
                    Log::info('CMS View: Response from generate_token: ' . json_encode($resp));

                    if ($resp !== null && isset($resp['token'])) {
                        Log::info('CMS View: New token generated: ' . $resp['token']);
                        $token = $resp['token'];
                        $response = Http::withToken(config('cms.api_key'))
                            ->post(config('cms.create_new_token'), [
                                'mac_address' => $mac_address,
                                'token' => $token,
                            ])
                            ->json();

                        Log::info('CMS View: Response from create_new_token: ' . json_encode($response));
                        Log::info('CMS View: Token created: ' . $token);

                        if ($response !== null && isset($response['message'])) {
                            Log::info('CMS View: Token created in CMS: ' . json_encode($response));
                        } else {
                            Log::error('CMS View: Error creating token in CMS: ' . json_encode($response));
                        }
                    } else {
                        Log::error('CMS View: Error generating new token: ' . json_encode($resp));
                    }
                }
            } elseif ($resp !== null && isset($resp['token'])) {
                Log::info('CMS View: Token found: ' . $resp['token']);
                $token = $resp['token'];
            }
        } catch (\Exception $e) {
            Log::error('CMS View: Error fetching token' . $e);
            $token = 'Error Fetching Token';
        }
    } else {
        Log::error('CMS View: Error fetching MAC address: ' . json_encode($resp));
        $token = 'Error Fetching Token';
    }

    if (!isset($token)) {
        $token = 'Error Fetching Token';
    }
@endphp

@section('content')
    @if ($token != 'Error Fetching Token')
        <div class="container d-flex justify-content-center align-items-center" style="min-height: 85vh;">
            <div class="card border-0 shadow-lg" style="width: 100%; max-width: 700px;">
                <div class="gradient-header p-4 d-flex align-items-center">
                    <div class="icon-container me-3">
                        <i class="la la-key fs-3"></i>
                    </div>
                    <h3 class="m-0 fw-bold">CMS Token</h3>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <div class="token-container position-relative mb-4">
                        <div class="token-label mb-2">Your Access Token</div>
                        <div class="code-container rounded position-relative">
                            <pre id="cms-token" class="mb-0 user-select-all p-4">{{ $token }}</pre>

                            <button id="copy-btn" class="btn copy-button" data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Copy token">
                                <i id="copy-icon" class="la la-copy"></i>
                            </button>
                        </div>
                        <div class="token-info mt-3">
                            <div class="d-flex align-items-center text-muted">
                                <i class="la la-info-circle me-2"></i>
                                <small>This token provides access to your CMS resources</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 d-flex justify-content-end">
                        <button onclick="window.history.back()" class="btn btn-outline-secondary me-3">
                            <i class="la la-arrow-left me-1"></i> Back
                        </button>
                        <a href="{{ backpack_url('dashboard') }}" class="btn btn-primary">
                            <i class="la la-stream nav-icon me-1"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="container d-flex justify-content-center align-items-center" style="min-height: 85vh;">
            <div class="alert alert-danger text-center" role="alert" style="max-width: 600px;">
                <h4 class="alert-heading">Error Fetching Token</h4>
                <p>We encountered an issue while trying to fetch your CMS token. Please try again later or contact support
                    if the issue persists.</p>
                <hr>
                <button onclick="window.history.back()" class="btn btn-outline-secondary me-3">
                    <i class="la la-arrow-left me-1"></i> Back
                </button>
                <a href="{{ backpack_url('dashboard') }}" class="btn btn-primary">
                    <i class="la la-stream nav-icon me-1"></i> Dashboard
                </a>
            </div>
        </div>
    @endif

    @push('after_styles')
        <style>
            :root {
                --primary-gradient-start: #e64747;
                --primary-gradient-end: #e9547e;
                --token-bg: #1c1c1ced;
                --card-bg: var(--tblr-navbar-bg);
                --text-primary: var(--tblr-primary-text);
                --text-secondary: var(--tblr-secondary-text);
                --text-token: #e2e8f0;
                --copy-button-bg: rgba(255, 255, 255, 0.15);
                --copy-button-hover: rgba(255, 255, 255, 0.25);
            }

            .card {
                border-radius: 1rem;
                overflow: hidden;
                background-color: var(--card-bg);
                transition: all 0.6s ease;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }

            .gradient-header {
                background: linear-gradient(135deg, var(--primary-gradient-start), var(--primary-gradient-end));
                color: white;
                border-bottom: none;
                border-radius: 1rem 1rem 0 0;
                position: relative;
            }

            .icon-container {
                width: 42px;
                height: 42px;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: rgba(255, 255, 255, 0.2);
                border-radius: 12px;
                backdrop-filter: blur(5px);
            }

            .token-label {
                font-weight: 600;
                font-size: 0.9rem;
                color: var(--text-secondary);
                margin-bottom: 0.5rem;
            }

            .code-container {
                position: relative;
                overflow: hidden;
                background-color: var(--token-bg);
                border-radius: 10px;
                border: 1px solid rgba(255, 255, 255, 0.05);
                transition: all 0.2s ease;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            #cms-token {
                font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
                white-space: pre-wrap;
                word-break: break-all;
                color: var(--text-token);
                font-size: 1.25rem;
                letter-spacing: 0.5px;
                background-color: transparent;
            }

            .copy-button {
                position: absolute;
                top: 12px;
                right: 12px;
                width: 40px;
                height: 40px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: var(--copy-button-bg);
                color: white;
                border: none;
                backdrop-filter: blur(5px);
                transition: all 0.2s ease;
            }

            .copy-button:hover {
                background-color: var(--copy-button-hover);
                transform: translateY(-2px);
            }

            .copy-success {
                background-color: #dd214a !important;
            }

            .token-info {
                color: var(--text-secondary);
            }

            .btn-primary {
                background: linear-gradient(135deg, var(--primary-gradient-start), var(--primary-gradient-end));
                border: none;
                padding: 0.6rem 1.2rem;
                border-radius: 0.5rem;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .btn-primary:hover {
                box-shadow: 0 4px 12px rgba(230, 71, 71, 0.364);
                transform: translateY(-2px);
            }

            .btn-outline-secondary {
                border-color: #d1d5db;
                color: var(--text-secondary);
                padding: 0.6rem 1.2rem;
                border-radius: 0.5rem;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .btn-outline-secondary:hover {
                background-color: #f3f4f6;
                border-color: #d1d5db;
                color: var(--text-secondary);
                transform: translateY(-2px);
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .card-body {
                    padding: 1.5rem !important;
                }

                #cms-token {
                    font-size: 1rem !important;
                    padding: 1rem !important;
                }

                .icon-container {
                    width: 36px;
                    height: 36px;
                }
            }

            /* Animation classes */
            .fade-in-up {
                animation: fadeInUp 0.6s ease forwards;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .pulse {
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(71, 118, 230, 0.4);
                }

                70% {
                    box-shadow: 0 0 0 10px rgba(71, 118, 230, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(71, 118, 230, 0);
                }
            }
        </style>
    @endpush

    @push('after_scripts')
        <script>
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            function copyCmsToken() {
                const tokenText = document.getElementById('cms-token').innerText;
                const copyBtn = document.getElementById('copy-btn');
                const copyIcon = document.getElementById('copy-icon');

                // Use modern clipboard API if available
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(tokenText).then(() => {
                        showCopySuccess(copyBtn, copyIcon);
                    });
                } else {
                    // Fallback for older browsers
                    const textarea = document.createElement('textarea');
                    textarea.value = tokenText;
                    textarea.setAttribute('readonly', '');
                    textarea.style.position = 'absolute';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);

                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);

                    showCopySuccess(copyBtn, copyIcon);
                }
            }

            function showCopySuccess(copyBtn, copyIcon) {
                // Show visual feedback
                copyBtn.classList.add('copy-success');
                copyIcon.classList.remove('la-copy');
                copyIcon.classList.add('la-check');

                // Hide tooltip temporarily and show success message
                const tooltip = bootstrap.Tooltip.getInstance(copyBtn);
                tooltip.hide();
                copyBtn.setAttribute('data-bs-original-title', 'Copied!');
                tooltip.show();

                // Reset after 2 seconds
                setTimeout(function() {
                    copyBtn.classList.remove('copy-success');
                    copyIcon.classList.remove('la-check');
                    copyIcon.classList.add('la-copy');
                    copyBtn.setAttribute('data-bs-original-title', 'Copy token');
                    tooltip.hide();
                }, 2000);
            }

            // Add click event listener to the copy button
            document.getElementById('copy-btn').addEventListener('click', copyCmsToken);

            // Add animation to card and elements on page load
            document.addEventListener('DOMContentLoaded', function() {
                const card = document.querySelector('.card');
                const codeContainer = document.querySelector('.code-container');

                // Add animation classes
                card.classList.add('fade-in-up');

                // Add subtle pulse to the token container after a delay
                setTimeout(function() {
                    codeContainer.classList.add('pulse');
                }, 1000);

                // Check if dark mode is enabled via browser preference or system setting
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.body.classList.add('dark-mode');
                }

                // Listen for changes in color scheme preference
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
                    if (event.matches) {
                        document.body.classList.add('dark-mode');
                    } else {
                        document.body.classList.remove('dark-mode');
                    }
                });
            });
        </script>
    @endpush
@endsection
