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
    <div class="large-3 columns">
      <p>
      <div id='cssmenu'>

        <ul>
          <li><a href='index.php'><span>Homepage</span></a></li>
          <li><a href='home.php'><span>My Dashboard</span></a></li>
          <li class='active'><a href='setup.php'><span>Setup Instructions</span></a></li>
          <li><a href='modules.php'><span>In-game Modules</span></a></li>
        </ul>
      </div>

    </div>

    <div class="large-9 columns">

      <div class="row">
        <div class="large-12 columns">
          <h2 style="color:#1b9bff">Setup CraftNanny</h2>

        </div>
      </div>

      <div class="row">
        <div class="large-12 columns">
          <h4>1. <a href="signup.php">Create an account</a></h4>
          <h4>2. Choose what module you want to setup <a href="modules.php">from this list</a></h4>
          <h4>3. On an in-game advanced computer run:</h4>
          <div style="padding:5px;width:400px;margin-left:30px;">
            wget https://craftnanny.org/modules/installer.lua
          </div>
          <h4>4. Select version you want to use for CraftNanny</h4>
          <img src="assets/img/setup/select_version.png" />
          <p>
          <h4>5. Select a module from the on-screen list</h4>
          <img src="assets/img/setup/select_module.png" />
          <p>
          <h4>6. Name the module. This will identify it on the website</h4>
          <img src="assets/img/setup/name_module.png" />
          <p>
          <h4>7. Login to your CraftNanny Account</h4>
          <img src="assets/img/setup/register_module.png" />
          <p>
          <h4>8. Your module will be installed. After that, it will appear on the website.</h4>
          <img src="assets/img/setup/complete.png" />
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
          <p>
          <h2 style="font-weight:bold;color:#cccccc;">CraftNanny.org</h2>
          <p style="color:#cccccc;font-size:18px;">Contribute to this open-source project on GitHub!</p>
          <a href="https://github.com/SkyNetCloud/CraftNanny"><img src="assets/img/git.png" style="width:100px;"></a>
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

  <script src="assets/js/vendor/jquery.js"></script>
  <script src="assets/js/foundation.min.js"></script>
  <script>
    $(document).foundation();
  </script>

</body>

</html>