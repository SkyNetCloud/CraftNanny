<?php
$members = json_decode(file_get_contents('https://discordapp.com/api/guilds/991301963469819975/widget.json'), true)['members'];
$membersCount = 1;
foreach ($members as $member) {
    if ($member['status'] == 'online') {
        $membersCount++;
    }
}
echo "Number of members: " . $membersCount;
?>

<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CraftNanny</title>
    <link rel="stylesheet" href="assets/css/foundation.css" />
    <script src="assets/js/vendor/modernizr.js"></script>
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-66224425-1', 'auto');
  ga('send', 'pageview');

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
              <li>Track players</li>
              <li>Monitor energy and fluid storage.</li>
              <li>Control redstone outputs</li>
              <li>Create rule-based events (redstone triggers, emails)</li>
              <li>More modules in development</li>
            </ul>

            
            <div class="row">
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

        <hr>

        <div class="discord-invite">
    <h5 class="discord-invite-text">You have been invited to join a server</h5>
    <div class="discord-invite-body">
        <div class="discord-invite-image"></div>
        <div class="discord-invite-details">
            <h3 class="discord-invite-name">
<!--Uncomment if server is verified
                <div class="discord-invite-verified">
                    <svg class="discord-invite-verified-svg" aria-hidden="false" width="16" height="16" viewBox="0 0 16 15.2"><path fill="currentColor" fill-rule="evenodd" d="m16 7.6c0 .79-1.28 1.38-1.52 2.09s.44 2 0 2.59-1.84.35-2.46.8-.79 1.84-1.54 2.09-1.67-.8-2.47-.8-1.75 1-2.47.8-.92-1.64-1.54-2.09-2-.18-2.46-.8.23-1.84 0-2.59-1.54-1.3-1.54-2.09 1.28-1.38 1.52-2.09-.44-2 0-2.59 1.85-.35 2.48-.8.78-1.84 1.53-2.12 1.67.83 2.47.83 1.75-1 2.47-.8.91 1.64 1.53 2.09 2 .18 2.46.8-.23 1.84 0 2.59 1.54 1.3 1.54 2.09z"></path></svg>
                    <div class="discord-invite-verified-tick">
                        <svg class="discord-invite-verified-tick-svg" aria-hidden="false" width="16" height="16" viewBox="0 0 16 15.2"><path d="M7.4,11.17,4,8.62,5,7.26l2,1.53L10.64,4l1.36,1Z" fill="currentColor"></path></svg>
                    </div>
                </div>
-->
                SERVER-NAME
            </h3>
            <div class="discord-invite-counts">
                <i class="discord-invite-status-icon discord-invite-online-icon"></i>
                <strong class="discord-invite-count"><?php echo $membersCount?> Online</strong>
                <i class="discord-invite-status-icon discord-invite-offline-icon"></i>
                <strong class="discord-invite-count"><?php echo $membersCount?> Members</strong>
            </div>
        </div>
        <a type="button" class="discord-invite-join-button" href="https://discord.gg/INVITE-CODE">
            Join
        </a>
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
              <img src="https://crafatar.com/renders/body/283502a2-4134-454c-bb47-39c3875b0dd4" style="height:200px;">
            </div>
            <div class="large-4 columns" style="height:200px">
              <p><h2 style="font-weight:bold;color:#cccccc;">CraftNanny.org</h2>
              <p style="color:#cccccc;font-size:18px;">Contribute to this open-source project on GitHub!</p>
              <a href="https://github.com/skynetcloud/CraftNanny"><img src="assets/img/git.png" style="width:100px;"></a>
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
                <li><a href="home.php" style="color:#cccccc">My Dashboard</a>  </li>
                <li><a href="setup.php" style="color:#cccccc">Setup Instructions</a>  </li>
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

	
  </body>
</html>
