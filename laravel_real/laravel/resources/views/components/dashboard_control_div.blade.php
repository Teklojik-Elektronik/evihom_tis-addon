<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Guide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #e54646;
            --primary-hover: #a33030;
            --secondary-color: #8b6464;
            --success-color: #059669;
            --background-gradient: linear-gradient(135deg, #ea6666 0%, #a24b4b 100%);
            --card-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --step-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --text-primary: #371f1f;
            --text-secondary: #806b6b;
            --border-color: #ebe5e5;
            --card-bg: #fcf4f4;
            --step-bg: linear-gradient(145deg, #ffffff, #f8f4f4);
        }

        [data-theme="dark"] {
            --primary-color: #ff5555;
            --primary-hover: #ff3333;
            --secondary-color: #a88888;
            --success-color: #10b981;
            --background-gradient: linear-gradient(135deg, #2a1a1a 0%, #1a1010 100%);
            --card-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            --step-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.15);
            --text-primary: #f5f5f5;
            --text-secondary: #cccccc;
            --border-color: #444444;
            /* --card-bg: rgba(40, 40, 40, 0.95); */
            --card-bg: #221e26;
            --step-bg: linear-gradient(145deg, #2d2833, #2a2631);
            --step-bg-hover: linear-gradient(145deg, #3a3a3a, #313131);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            line-height: 1.6;
            color: var(--text-primary);
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
            transition: all 0.3s ease;
        }

        .card-body {
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            position: relative;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .step {
            background: var(--step-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: var(--step-shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .step:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
        }

        .step:hover::before {
            transform: scaleX(1);
        }

        .step-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .step-number {
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 15px;
            box-shadow: 0 4px 12px rgba(229, 70, 70, 0.3);
        }

        .step h2 {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .step-content {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.7;
        }

        .action-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
            position: relative;
        }

        .action-link:hover {
            background: var(--primary-color);
            color: white;
            text-decoration: none;
        }

        .highlight {
            background: linear-gradient(120deg, rgba(229, 70, 70, 0.1) 0%, rgba(229, 70, 70, 0.1) 100%);
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .icon {
            margin-right: 8px;
            color: var(--primary-color);
        }

        .progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 4px;
            background: var(--primary-color);
            transition: width 0.3s ease;
            z-index: 1000;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step:nth-child(2) {
            animation-delay: 0.2s;
        }

        .step:nth-child(3) {
            animation-delay: 0.4s;
        }

        .step:nth-child(4) {
            animation-delay: 0.6s;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }

            .card-body {
                padding: 25px;
            }

            h1 {
                font-size: 2rem;
                margin-bottom: 2rem;
            }

            .step {
                padding: 20px;
            }

            .step-header {
                flex-direction: column;
                text-align: center;
            }

            .step-number {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="progress-bar" id="progressBar"></div>
    <div class="container">
        <div class="tab-content">
            <div class="tab-pane fade show active">
                <div class="card animate__animated animate__fadeIn">
                    <div class="card-body">
                        <h1><i class="fas fa-rocket icon"></i>{{ __('messages.quick_guide') }}</h1>

                        <div class="step">
                            <div class="step-header">
                                <div class="step-number">1</div>
                                <h2><i class="fas fa-search icon"></i>{{ __('messages.scan_network') }}</h2>
                            </div>
                            <div class="step-content">
                                <p>{!! __('messages.scan_network_desc', ['url' => backpack_url('device')]) !!}</p>
                                <br>
                                <p>{!! __('messages.scan_network_desc2', ['url' => backpack_url('device/create')]) !!}</p>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-header">
                                <div class="step-number">2</div>
                                <h2><i class="fas fa-magic icon"></i>{{ __('messages.auto_create_appliances_title') }}</h2>
                            </div>
                            <div class="step-content">
                                <p>{!! __('messages.auto_create_appliances_desc', ['url' => backpack_url('device')]) !!}</p>
                                <br>
                                <p>{{ __('messages.auto_create_appliances_desc2') }}</p>
                                <br>
                                <p>{!! __('messages.auto_create_appliances_desc3', [
                                    'url1' => backpack_url('appliance'),
                                    'url2' => backpack_url('appliance/create'),
                                    'url3' => backpack_url('appliance-channels')
                                ]) !!}</p>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-header">
                                <div class="step-number">3</div>
                                <h2><i class="fas fa-cloud-upload-alt icon"></i>{{ __('messages.publish_configuration') }}</h2>
                            </div>
                            <div class="step-content">
                                <p>{!! __('messages.publish_configuration_desc', ['url' => backpack_url('appliance')]) !!}</p>
                                <br>
                                <p>{!! __('messages.publish_configuration_desc2') !!}</p>
                                <br>
                                <p>{!! __('messages.publish_configuration_desc3') !!}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simulate progress as user scrolls
        function updateProgressBar() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;
            document.getElementById('progressBar').style.width = scrollPercent + '%';
        }

        window.addEventListener('scroll', updateProgressBar);

        // Add intersection observer for step animations
        const steps = document.querySelectorAll('.step');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'slideInUp 0.6s ease-out forwards';
                }
            });
        }, {
            threshold: 0.1
        });

        steps.forEach(step => {
            observer.observe(step);
        });

        // Mock backpack_url function for demo
        function backpack_url(route) {
            console.log('Navigation to:', route);
            // In your actual implementation, this would handle the routing
            return '#' + route;
        }

        // Add smooth scrolling for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
