<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CraftNanny</title>
    <link rel="stylesheet" href="assets/css/foundation.css" />
    <script src="assets/js/vendor/modernizr.js"></script>
</head>
<body>

    <!-- Top Bar -->
    <div class="large-12 columns top_bar">
        <div class="row">
            <span style="font-weight: bold; font-size: 36px; color: #1b9bff;">CraftNanny</span>
            <span style="font-weight: bold; font-size: 16px; color: #ffffff;">
                Monitor and Control Minecraft Online through ComputerCraft
            </span>
        </div>
    </div>

    <!-- Thin Bar -->
    <div class="large-12 columns thin_bar"></div>

    <!-- Main Content Row -->
    <div class="row">
        
        <!-- Account Menu Placeholder -->
        <div id="account-menu-placeholder"></div>

        <!-- Connected Modules Section -->
        <div class="large-9 columns">
            <div class="module_header">
                <h3 style="color: #0099FF;">Connected reactor modules:</h3>
                <p>In-game modules update every 30 seconds.</p>
            </div>

            <ul id="connected_modules"></ul>

            <div class="no_connected_modules">
                <h3 style="color: #0099FF;">Reactor Modules</h3>
                <h4 style="color: #CC0000; font-weight: bold;">
                    There are no reactor modules connected to this account. 
                    Setup instructions are available <a href="setup.php">here</a>.
                </h4>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <!-- Footer Top -->
        <div class="large-12 columns footer_top">
            <div class="row">
                <div class="large-6 columns"></div>
                <div class="large-6 columns"></div>
            </div>
        </div>

        <!-- Footer Middle -->
        <div class="large-12 columns footer_middle">
            <div class="row">
                <!-- Image and GitHub Link -->
                <div class="large-2 columns">
                    <img src="https://mc-heads.net/body/SkyNetCloud" style="height: 200px;" alt="SkyNetCloud">
                </div>
                <div class="large-4 columns" style="height: 200px;">
                    <h2 style="font-weight: bold; color: #cccccc;">CraftNanny.org</h2>
                    <p style="color: #cccccc; font-size: 18px;">Contribute to this open-source project on GitHub!</p>
                    <a href="https://github.com/jaranvil/CraftNanny">
                        <img src="assets/img/git.png" style="width: 100px;" alt="GitHub">
                    </a>
                </div>
                <div class="large-6 columns"></div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="large-12 columns footer_bottom">
            <div class="row">
                <div class="large-3 columns"></div>
                <div class="large-9 columns" style="color: #cccccc;">
                    <ul class="inline-list right" style="margin-top: 40px;">
                        <li><a href="home.php" style="color: #cccccc;">My Dashboard</a></li>
                        <li><a href="setup.php" style="color: #cccccc;">Setup Instructions</a></li>
                        <li><a href="modules.php" style="color: #cccccc;">Modules</a></li>
                        <li><a href="https://github.com/skynetcloud/CraftNanny/issues" style="color: #cccccc;">Report Issues</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript Includes -->
    <script src="assets/js/vendor/jquery.js"></script>
    <script src="assets/js/foundation.min.js"></script>
    <script>
        $(document).foundation();
    </script>
    <script src="assets/js/menu.js"></script>
    <script src="assets/js/login_check.js"></script>
    <script src="assets/js/block.js"></script>
    <script src="assets/js/reactor.js"></script>

</body>
</html>
