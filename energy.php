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

  <div class="large-12 columns top_bar">
    <div class="row">
      <span style="font-weight:bold;font-size:36px;color:#1b9bff">
        CraftNanny
      </span>
      <span style="font-weight:bold;font-size:16px;color:#ffffff">
        Monitor and Control Minecraft Online through ComputerCraft
      </span>
    </div>
  </div>
  <div class="large-12 columns thin_bar">
  </div>


  <div class="row">
    <div id="account-menu-placeholder">
    </div>


    <div class="large-9 columns">
      <div class="module_header">
        <h3 style="color:#0099FF">Connected energy modules:</h3>
        Information updated every 30 seconds when modules are online.<p>
      </div>

      <ul id="connected_modules">

      </ul>

      <div class="no_connected_modules">
        <h3 style="color:#0099FF">Energy Modules</h3>
        <h4 style="color:#CC0000;font-weight:bold;">
          There are no energy modules connected to this account. Setup instructions are available <a href="setup.php">here</a>
        </h4>
        <div class="row">
          <div class="large-6 columns">
            <h4>What it does</h4>
            <ul>
              <li>Logs the storage percent of attached periperals</li>
              <li>IC2, Thermal Expansion, and EnderIO support</li>
            </ul>
          </div>
          <div class="large-6 columns">
            <h4>Setup</h4>
            <ul>
              <li>Place advanced computer directly next to a stroage device or connected with modems</li>
              <li>
                Run module installer <a href="setup.php">from here.</a>
              </li>
            </ul>

          </div>
        </div>
        <div class="row">
          <div class="large-6 columns">
            <img src="assets/img/mods/energy.png">

          </div>
          <div class="large-6 columns">
            <img src="assets/img/screenshots/energy.PNG">
          </div>
        </div>
      </div>

    </div>
  </div>

  <footer>
    <div class="large-12 columns footer_top">
      <div class="row">
        <div class="large-6 columns">

        </div>
        <div class="large-6 columns">

            </div>
          </div>
        </div>
        <div class="large-12 columns footer_middle">
          <div class="row">
            <div class="large-2 columns">
              <img src="https://mc-heads.net/body/SkyNetCloud" style="height:200px;">
            </div>
            <div class="large-4 columns" style="height:200px">
              <p><h2 style="font-weight:bold;color:#cccccc;">CraftNanny.org</h2>
              <p style="color:#cccccc;font-size:18px;">Contribute to this open-source project on GitHub!</p>
              <a href="https://github.com/SkyNetCloud/CraftNanny"><img src="assets/img/git.png" style="width:100px;"></a>
              <a href="https://discord.gg/5CfvE3nA6N"><img src="assets/img/discord.png" style="width:100px;"></a>
            </div>
            <div class="large-6 columns">

        </div>
      </div>
    </div>
    <div class="large-12 columns footer_bottom">
      <div class="row">
        <div class="large-3 columns">

        </div>
        <div class="large-9 columns" style="color:#cccccc">
          <ul class="inline-list right" style="margin-top:40px;">
            <li><a href="home.php" style="color:#cccccc">My Dashboard</a> </li>
            <li><a href="setup.php" style="color:#cccccc">Setup Instructions</a> </li>
            <li><a href="modules.php" style="color:#cccccc">Modules</a> </li>
            <li><a href="https://github.com/skynetcloud/CraftNanny/issues" style="color:#cccccc">Report Issues </a></li>
          </ul>
        </div>
      </div>
    </div>
  </footer>


  <script src="assets/js/vendor/jquery.js"></script> <!-- Load jQuery first -->
  <script src="assets/js/foundation.min.js"></script>
  <script>
    $(document).foundation(); // Initialize Foundation
  </script>


  <script src="assets/js/login_check.js"></script>
  <script src="assets/js/block.js"></script>
  <script src="assets/js/energy.js"></script>
  <script src="assets/js/menu.js"></script>
</body>

</html>