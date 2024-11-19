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
  <div class="large-12 columns thin_bar"></div>

  <div class="row">
  <div id="account-menu-placeholder">
  </div>


    <div class="large-9 columns">
      <div class="row">
        <h3 style="color:#0099FF" id="welcome"></h3>
        <span style="font-weight:bold;font-size:16px;color:red">For bug reports, suggestions and questions <a href="https://github.com/skynetcloud/CraftNanny/issues">post here</a></span>
        <!--<div class="large-12 columns notifications" id="">
          <strong style="color:#0099FF">Active Rules:</strong>
          <p>
          Coming soon. 
          Examples: energy storage reaches 0%, set redstone module output
        </div>-->
      </div>
      <p>
      <div class="row">
        <!-- <div class="large-12 columns notifications" id="">
          <strong style="color:#0099FF">Email Notifications:</strong>
          <p>
            Coming soon. 
            Set email alerts for certain events. Energy or fluid reach x% or player x enters base.
        </div> -->
      </div>

      <div class="row">

        <div class="large-4 columns modules" id="energy_modules">
          <strong style="color:#0099FF">Energy Modules:</strong>
          <div class="no_modules" id="no_energy_modules">
            <span>No Energy Modules Connected</span>
          </div>
        </div>

        <div class="large-4 columns modules" id="fluid_modules">
          <strong style="color:#0099FF">Fluid Modules:</strong>
          <div class="no_modules" id="no_fluid_modules">
            <span>No Fluid Modules Connected</span>
          </div>
        </div>
        
        <div class="large-4 columns modules" id="redstone_modules">
          <strong style="color:#0099FF">Redstone Modules:</strong>
          <div class="no_modules" id="no_redstone_modules">
            <span>No Redstone Modules Connected</span>
          </div>
        </div>
      </div>

      <div class="row">

        <!-- <div class="large-4 columns modules" id="energy_modules">
          <strong style="color:#0099FF">Rednet Modules</strong>
          <div class="no_modules" id="no_player_modules">
            <span>No Rednet Modules Connected</span>
          </div>
        </div> -->
        <div class="large-4 columns">
          <!--<strong style="color:#0099FF">stub</strong>-->
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
          <h2 style="font-weight:bold;color:#cccccc;">CraftNanny.org</h2>
          <p style="color:#cccccc;font-size:18px;">Contribute to this open-source project on GitHub!</p>
          <a href="https://github.com/SkyNetCloud/CraftNanny"><img src="assets/img/git.png" style="width:100px;"></a>
          <a href="https://discord.gg/5CfvE3nA6N"><img src="assets/img/discord.png?v=2.1" style="width:100px";></a>
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
            <li><a href="home.php" style="color:#cccccc">My Dashboard</a></li>
            <li><a href="setup.php" style="color:#cccccc">Setup Instructions</a></li>
            <li><a href="modules.php" style="color:#cccccc">Modules</a></li>
            <li><a href="https://github.com/SkyNetCloud/CraftNanny/issues" style="color:#cccccc">Report Issues</a></li>
          </ul>
        </div>
      </div>
    </div>
  </footer>
  <script src="assets/js/vendor/jquery.js"></script>
  <script src="assets/js/foundation.min.js"></script>
  <script>
    $(document).foundation();
  </script>
  <script src="assets/js/login_check.js"></script>
  <script src="assets/js/home.js"></script>
  <script src="assets/js/menu.js"></script>
</body>

</html>
