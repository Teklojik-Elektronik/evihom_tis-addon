<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Bill - Home Assistant</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --body-bg: #f8f9fa;
            --card-bg: #fff;
            --text-color: #474d53;
            --input-bg: #fff;
            --input-border: #ced4da;
            --btn-primary: #4e73df;
            --btn-primary-hover: #3a5fc4;
            --btn-success: #28a745;
            --btn-success-hover: #218838;
            --btn-danger: #dc3545;
            --btn-danger-hover: #c82333;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --border-radius: 8px;
            --summer-color: #ff6b35;
            --winter-color: #4a90e2;
        }

        [data-theme="dark"] {
            --body-bg: #1a1d21;
            --card-bg: #2d343c;
            --text-color: #e9ecef;
            --input-bg: #343a40;
            --input-border: #495057;
            --btn-primary: #4e73df;
            --btn-primary-hover: #5a7ae2;
            --btn-success: #28a745;
            --btn-success-hover: #34ce57;
            --btn-danger: #dc3545;
            --btn-danger-hover: #e4606d;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--body-bg);
            color: var(--text-color);
            transition: var(--transition);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 100%;
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 15px;
        }

        header {
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            background-color: var(--card-bg);
            margin-bottom: 40px;
            transition: var(--transition);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-title {
            font-size: 24px;
            font-weight: 600;
        }

        .theme-control {
            display: flex;
            gap: 10px;
        }

        .theme-btn {
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            background-color: var(--card-bg);
            color: var(--text-color);
            border: 1px solid var(--input-border);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .theme-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .theme-btn.active {
            background-color: var(--btn-primary);
            color: white;
            border-color: var(--btn-primary);
        }

        main {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 20px 0;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
            transition: var(--transition);
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid var(--input-border);
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
        }

        .card-body {
            padding: 30px 20px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title.summer {
            color: var(--summer-color);
        }

        .section-title.winter {
            color: var(--winter-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border-radius: 4px;
            border: 1px solid var(--input-border);
            background-color: var(--input-bg);
            color: var(--text-color);
            transition: var(--transition);
            font-size: 16px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--btn-primary);
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.25);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid transparent;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--btn-primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--btn-primary-hover);
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .btn-success {
            background-color: var(--btn-success);
            color: white;
        }

        .btn-success:hover {
            background-color: var(--btn-success-hover);
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .btn-danger {
            background-color: var(--btn-danger);
            color: white;
            padding: 8px 12px;
            font-size: 14px;
        }

        .btn-danger:hover {
            background-color: var(--btn-danger-hover);
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--text-color);
            border-color: var(--input-border);
        }

        .btn-outline:hover {
            background-color: var(--input-bg);
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .sliding-rate-item {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
            transition: var(--transition);
        }

        .sliding-rate-item:hover {
            box-shadow: var(--box-shadow);
        }

        .sliding-rate-item.summer {
            border-left: 4px solid var(--summer-color);
        }

        .sliding-rate-item.winter {
            border-left: 4px solid var(--winter-color);
        }

        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .add-item-container {
            text-align: center;
            margin: 20px 0;
        }

        .submit-container {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--input-border);
        }

        .rate-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .rate-number {
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .rate-number.summer {
            background-color: var(--summer-color);
        }

        .rate-number.winter {
            background-color: var(--winter-color);
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .card {
                margin: 0 15px;
            }

            .sliding-rate-item {
                padding: 15px;
            }

            .remove-btn {
                position: static;
                margin-top: 10px;
                align-self: flex-start;
            }

            .rate-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        .alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-left: 4px solid #28a745;
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="header-content">
                <h1 class="header-title">Electricity Bill</h1>
                <div class="theme-control">
                    <button class="theme-btn" data-theme="light">
                        <i class="fas fa-sun"></i> Light
                    </button>
                    <button class="theme-btn" data-theme="dark">
                        <i class="fas fa-moon"></i> Dark
                    </button>
                    <button class="theme-btn" data-theme="system">
                        <i class="fas fa-laptop"></i> System
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Configure Electricity Billing Rates</h3>
            </div>
            <div class="card-body">
                <div id="alerts-container"></div>

                <form id="billing-form">
                    <input type="hidden" name="_token" id="csrf-token" value="{{ csrf_token() }}">
                    <!-- Summer Tiered Rates Section -->
                    <div class="form-section">
                        <h4 class="section-title summer">
                            <i class="fas fa-sun"></i> Summer Tiered Consumption Rates
                        </h4>
                        <div id="summer-rates-container">
                            <!-- Initial summer rate item -->
                            <div class="sliding-rate-item summer" data-index="0" data-season="summer">
                                <div class="rate-header">
                                    <div class="rate-number summer">1</div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="summer_min_kw_0">Minimum KW Limit</label>
                                        <input type="number" name="summer_min_kw[]" id="summer_min_kw_0"
                                            class="form-control" value="0" min="0" step="0.01" disabled
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="summer_price_per_kw_0">Price per KW (Currency)</label>
                                        <input type="number" name="summer_price_per_kw[]" id="summer_price_per_kw_0"
                                            class="form-control" placeholder="e.g., 0.12" min="0" step="0.001"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="add-item-container">
                            <button type="button" id="add-summer-rate-btn" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Summer Rate Tier
                            </button>
                        </div>
                    </div>

                    <!-- Winter Tiered Rates Section -->
                    <div class="form-section">
                        <h4 class="section-title winter">
                            <i class="fas fa-snowflake"></i> Winter Tiered Consumption Rates
                        </h4>
                        <div id="winter-rates-container">
                            <!-- Initial winter rate item -->
                            <div class="sliding-rate-item winter" data-index="0" data-season="winter">
                                <div class="rate-header">
                                    <div class="rate-number winter">1</div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="winter_min_kw_0">Minimum KW Limit</label>
                                        <input type="number" name="winter_min_kw[]" id="winter_min_kw_0"
                                            class="form-control" value="0" min="0" step="0.01" disabled
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="winter_price_per_kw_0">Price per KW (Currency)</label>
                                        <input type="number" name="winter_price_per_kw[]" id="winter_price_per_kw_0"
                                            class="form-control" placeholder="e.g., 0.15" min="0" step="0.001"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="add-item-container">
                            <button type="button" id="add-winter-rate-btn" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Winter Rate Tier
                            </button>
                        </div>
                    </div>

                    <div class="submit-container">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Billing Configuration
                        </button>
                        <a href="homeassistant.local:8123" class="btn btn-outline" style="margin-left: 10px;">
                            <i class="fas fa-ban"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme management
            const themeButtons = document.querySelectorAll('.theme-btn');

            function applyTheme(theme) {
                if (theme === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
                } else {
                    document.documentElement.setAttribute('data-theme', theme);
                }

                themeButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.theme === theme);
                });

                localStorage.setItem('backpack-theme', theme);
            }

            themeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const theme = button.dataset.theme;
                    applyTheme(theme);
                });
            });

            const savedTheme = localStorage.getItem('backpack-theme') || 'system';
            applyTheme(savedTheme);

            if (savedTheme === 'system') {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
                    if (localStorage.getItem('backpack-theme') === 'system') {
                        document.documentElement.setAttribute('data-theme', event.matches ? 'dark' :
                            'light');
                    }
                });
            }

            // Seasonal rates management
            let summerRateIndex = 1;
            let winterRateIndex = 1;

            const summerRatesContainer = document.getElementById('summer-rates-container');
            const winterRatesContainer = document.getElementById('winter-rates-container');
            const addSummerRateBtn = document.getElementById('add-summer-rate-btn');
            const addWinterRateBtn = document.getElementById('add-winter-rate-btn');

            function updateRateNumbers(container, season) {
                const rateItems = container.querySelectorAll('.sliding-rate-item');
                rateItems.forEach((item, index) => {
                    const rateNumber = item.querySelector('.rate-number');
                    rateNumber.textContent = index + 1;

                    // Update IDs and names
                    const inputs = item.querySelectorAll('input');
                    inputs.forEach(input => {
                        const baseName = input.name.replace(/\[\]$/, '').replace(/\d+$/, '');
                        input.name = baseName + '[]';
                        input.id = baseName + '_' + index;
                    });

                    const labels = item.querySelectorAll('label');
                    labels.forEach(label => {
                        const forAttr = label.getAttribute('for');
                        if (forAttr) {
                            const baseName = forAttr.replace(/_\d+$/, '');
                            label.setAttribute('for', baseName + '_' + index);
                        }
                    });

                    item.setAttribute('data-index', index);
                });
            }

            function createRateItem(index, season) {
                const rateItem = document.createElement('div');
                rateItem.className = `sliding-rate-item ${season}`;
                rateItem.setAttribute('data-index', index);
                rateItem.setAttribute('data-season', season);

                rateItem.innerHTML = `
                    <div class="rate-header">
                        <div class="rate-number ${season}">${index + 1}</div>
                        <button type="button" class="btn btn-danger remove-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="${season}_min_kw_${index}">Minimum KW Limit</label>
                            <input type="number" name="${season}_min_kw[]" id="${season}_min_kw_${index}" class="form-control"
                                   placeholder="e.g., 200" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="${season}_price_per_kw_${index}">Price per KW (Currency)</label>
                            <input type="number" name="${season}_price_per_kw[]" id="${season}_price_per_kw_${index}" class="form-control"
                                   placeholder="e.g., ${season === 'summer' ? '0.14' : '0.18'}" min="0" step="0.001" required>
                        </div>
                    </div>
                `;

                // Add remove functionality
                const removeBtn = rateItem.querySelector('.remove-btn');
                removeBtn.addEventListener('click', function() {
                    rateItem.remove();
                    const container = season === 'summer' ? summerRatesContainer : winterRatesContainer;
                    updateRateNumbers(container, season);
                });

                return rateItem;
            }

            // Summer rates
            addSummerRateBtn.addEventListener('click', function() {
                const newRateItem = createRateItem(summerRateIndex, 'summer');
                summerRatesContainer.appendChild(newRateItem);
                summerRateIndex++;
                updateRateNumbers(summerRatesContainer, 'summer');
            });

            // Winter rates
            addWinterRateBtn.addEventListener('click', function() {
                const newRateItem = createRateItem(winterRateIndex, 'winter');
                winterRatesContainer.appendChild(newRateItem);
                winterRateIndex++;
                updateRateNumbers(winterRatesContainer, 'winter');
            });

            fetch("{{ route('get-bill-configs') }}", {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(configs => {
                    console.log('Fetched Billing Configuration:', configs);
                    const summerRates = configs.summer_rates;
                    const winterRates = configs.winter_rates;

                    // Populate summer rates
                    summerRates.forEach((rate, index) => {
                        if (index === 0) {
                            const rateItem = document.querySelector(".sliding-rate-item.summer");
                            rateItem.querySelector(`#summer_min_kw_${index}`).value = rate.min_kw;
                            rateItem.querySelector(`#summer_price_per_kw_${index}`).value = rate
                                .price_per_kw;
                        } else {
                            const rateItem = createRateItem(index, 'summer');
                            rateItem.querySelector(`#summer_min_kw_${index}`).value = rate.min_kw;
                            rateItem.querySelector(`#summer_price_per_kw_${index}`).value = rate
                                .price_per_kw;
                            summerRatesContainer.appendChild(rateItem);
                        }
                    });
                    summerRateIndex = configs.summer_rates.length;

                    // Populate winter rates
                    winterRates.forEach((rate, index) => {
                        if (index === 0) {
                            const rateItem = document.querySelector(".sliding-rate-item.winter");
                            rateItem.querySelector(`#winter_min_kw_${index}`).value = rate.min_kw;
                            rateItem.querySelector(`#winter_price_per_kw_${index}`).value = rate
                                .price_per_kw;
                        } else {
                            const rateItem = createRateItem(index, 'winter');
                            rateItem.querySelector(`#winter_min_kw_${index}`).value = rate.min_kw;
                            rateItem.querySelector(`#winter_price_per_kw_${index}`).value = rate
                                .price_per_kw;
                            winterRatesContainer.appendChild(rateItem);
                        }
                    });
                    winterRateIndex = configs.winter_rates.length;

                    updateRateNumbers(summerRatesContainer, 'summer');
                    updateRateNumbers(winterRatesContainer, 'winter');
                });

            // Form submission
            const form = document.getElementById('billing-form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const data = {};
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                // Collect summer rates
                let summerMinKwValues = formData.getAll('summer_min_kw[]');
                summerMinKwValues = [0, ...summerMinKwValues];
                const summerPricePerKwValues = formData.getAll('summer_price_per_kw[]');

                data.summer_rates = summerMinKwValues.map((minKw, index) => ({
                    min_kw: parseFloat(minKw),
                    price_per_kw: parseFloat(summerPricePerKwValues[index])
                }));

                // Collect winter rates
                let winterMinKwValues = formData.getAll('winter_min_kw[]');
                winterMinKwValues = [0, ...winterMinKwValues];
                const winterPricePerKwValues = formData.getAll('winter_price_per_kw[]');

                data.winter_rates = winterMinKwValues.map((minKw, index) => ({
                    min_kw: parseFloat(minKw),
                    price_per_kw: parseFloat(winterPricePerKwValues[index])
                }));

                console.log('Billing Configuration:', data);
                fetch("{{ route('configure-bill') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.getElementById('csrf-token').value,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        const alertsContainer = document.getElementById('alerts-container');
                        console.log('result:', data);

                        if (data.success) {
                            // Handle success response
                            alertsContainer.innerHTML = `
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Billing configuration saved successfully!</span>
                                </div>
                            `;
                        } else {
                            alertsContainer.innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span>${data.error}</span>
                                </div>
                            `;
                            // Handle error response
                            console.error('Error saving billing configuration:', data.message);
                        }
                    }).catch(err => {
                        submitButton.disabled = false;
                        submitButton.innerHTML =
                            '<i class="fas fa-save"></i> Save Billing Configuration';
                        console.error('Error:', err);
                        const alertsContainer = document.getElementById('alerts-container');
                        alertsContainer.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>An unexpected error occurred. Please try again later.</span>
                            </div>
                        `;
                    });

                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-save"></i> Save Billing Configuration';

                // Scroll to top to show the alert
                const alertsContainer = document.getElementById('alerts-container');
                alertsContainer.scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>

</html>
