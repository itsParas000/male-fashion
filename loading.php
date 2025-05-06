<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Loader</title>
    <style>
        /* Set white background for main content */
        body {
            margin: 0;
            background: #fff; /* White background */
            min-height: 100vh;
        }

        /* Pre-loader Container */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #FFFFFF; /* Black background for loader */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 1s ease-out; /* Smoother, longer transition */
            opacity: 1;
        }

        /* Loader Animation */
        .loading-animation {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Hide Pre-loader After Load */
        .loading-screen.hidden {
            opacity: 0;
            pointer-events: none;
        }

        /* Ensure SVG Scales Properly */
        .loading-animation img {
            width: 125px;
            height: 125px;
            animation: none;
        }
    </style>
    <script>
        // Show pre-loader immediately
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.querySelector('.loading-screen');
            loader.style.display = 'flex'; // Ensure it’s visible on load
        });

        // Hide pre-loader with delay when page is fully loaded
        window.addEventListener('load', () => {
            const loader = document.querySelector('.loading-screen');
            // Add a delay before starting the fade-out (3000ms = 3 seconds)
            setTimeout(() => {
                loader.className = 'loading-screen hidden';
                // Remove the loader after the fade-out transition completes
                setTimeout(() => loader.remove(), 500); // Matches transition duration
            }, 500); // Loader stays visible for 3 seconds after page load
        });
    </script>
</head>
<body>
    <div id="page-loader" class="loading-screen">
        <div class="loading-animation">
            <img src="assets/rings.svg" alt="loader" loading="eager">
        </div>
    </div>
</body>
</html>