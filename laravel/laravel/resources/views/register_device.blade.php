<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background-color: rgba(0, 0, 0, 0.03);
        }

        .registration-modal .modal-content {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eaeaea;
        }

        /* This is to ensure the modal appears right away without needing a trigger */
        .modal {
            background-color: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>

<body>
    <!-- Your original view goes here -->
    @include('vendor.backpack.ui.dashboard')

    <!-- Modal Component -->
    <div class="modal fade show registration-modal" id="registrationModal" tabindex="-1"
        aria-labelledby="registrationModalLabel" style="display: block;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-center p-4">
                    <h4 class="modal-title w-100" id="registrationModalLabel">Device Registration Required</h4>
                </div>
                <div class="modal-body p-4">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <p class="text-center mb-4">Please enter your product serial number and select your country to
                        activate this device.</p>

                    <form action="{{ route('register.device') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="serial_number" class="form-label">Serial Number</label>
                            <input type="text" class="form-control" id="serial_number" name="serial_number" required
                                autofocus>
                            <div class="form-text">Enter the serial number provided with your product.</div>
                        </div>

                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="" disabled selected>Select your country</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Please select the country where you are located.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Register Device</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Optional script to prevent modal from being closed
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('registrationModal'), {
                backdrop: 'static',
                keyboard: false
            });
            modal.show();
        });
    </script>
</body>

</html>
