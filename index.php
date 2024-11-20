<!doctype html>
<html class="no-js" lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CraftNanny</title>
  <link rel="stylesheet" href="assets/css/foundation.css" />
  <script src="assets/js/vendor/modernizr.js"></script>
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-9CDJ9R0PWL"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-9CDJ9R0PWL');
  </script>
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
    <p>
    <div class="large-6 columns">
      <p><img src="assets/img/screenshots/redstone.PNG" id="homepage_img">
    </div>
    <div class="large-6 columns">
      <p>
      <h2 style="color:#0099FF;font-weight:bold;">Your Minecraft Base. <br>Online.</h2>
      Use ComputerCraft to:
      <ul>
        <li>Monitor energy and fluid storage.</li>
        <li>Control redstone outputs</li>
        <li>Create rule-based events (redstone triggers, emails)</li>
        <li>More modules in development</li>
      </ul>


      <div id="Info" class="row" style="display:none;">
        <div class="large-6 columns" style="padding:45px;">
          <form action="signup.php">
            <button type="submit" id="login_btn">Sign Up</button>
          </form>
        </div>
        <div class="large-6 columns" style="padding:45px;">
          <form action="signin.php">
            <button type="submit" id="login_btn">Login</button>
          </form>
        </div>
      </div>

      <div id="Dashboard" class="row" style="display:none;">
        <div class="large-12 columns" style="padding:45px;">
          <form action="home.php">
            <button type="submit" id="login_btn">Go to Dashboard</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <hr>

  <div class="row">
    <div class="large-4 columns">
      <h3 style="color:#0099FF">How it works</h3>

      <ul style="list-style-type:decimal">
        <li>Create an account <a href="signup.php" style="font-weight:bold">here.</a></li>
        <li>Install in-game modules from <a href="modules.php" style="font-weight:bold">this list.</a></li>
        <li>Control modules from <a href="home.php" style="font-weight:bold">the dashboard</a></li>
      </ul>
      <p><a href="setup.php" style="font-weight:bold">Full Setup Intructions</a>
    </div>
    <div class="large-4 columns">
      <h3 style="color:#0099FF">Mod Requirements:</h3>
      <ul>
        <li>ComputerCraft 1.6 +</li>
        <li>OpenPeripherals (for some modules)</li>
      </ul>
    </div>
    <div class="large-4 columns">
      <h3 style="color:#0099FF">Feedback</h3>
      <ul>
        This site is brand new and we would love your input, bug reports or help. Visit or <a href="https://github.com/skynetcloud/CraftNanny/issues">issues page</a>.
      </ul>
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
  <script src="assets/js/foundation/foundation.orbit.js"></script>
  <script>
    $(document).foundation();
  </script>

  <script>
    // Function to check if user is already logged in based on the presence of a login cookie
    // Function to check if user is already logged in based on the presence of a login cookie
    function checkLoggedIn() {
      // Retrieve the value of the login cookie
      var loginCookie = getCookie("logger_token");

      // Check if the login cookie exists and has a non-empty value
      return loginCookie && loginCookie.trim() !== "";
    }

    // Function to retrieve the value of a cookie by its name
    function getCookie(cookieName) {
      var name = cookieName + "=";
      var decodedCookie = decodeURIComponent(document.cookie);
      var cookieArray = decodedCookie.split(';');
      for (var i = 0; i < cookieArray.length; i++) {
        var cookie = cookieArray[i];
        while (cookie.charAt(0) === ' ') {
          cookie = cookie.substring(1);
        }
        if (cookie.indexOf(name) === 0) {
          return cookie.substring(name.length, cookie.length);
        }
      }
      return null;
    }

    // Check if user is already logged in
    if (checkLoggedIn()) {
      // Hide the login and sign-up forms
      document.getElementById("Info").style.display = "none";
      // Show the Dashboard button
      document.getElementById("Dashboard").style.display = "block";
    } else {
      // Show the login and sign-up forms
      document.getElementById("Info").style.display = "block";
      // Hide the Dashboard button
      document.getElementById("Dashboard").style.display = "none";
    }
  </script>
</body>

</html>