


-----------------PASTEBINs--------------------------

fluid_module_dev = "SkyNetCloud/CraftNanny/master/modules/energy.dev.lua"
energy_module_dev = "SkyNetCloud/CraftNanny/master/modules/fluid.dev.lua"

hash_api = "SkyNetCloud/CraftNanny/master/modules/hash_api.lua"
startup = "SkyNetCloud/CraftNanny/master/modules/startup.lua"
---------------------------------------------
term.clear()
local token = ''
local module_name = ''
local username = ''
local type = ''
local updating = false



function draw_text_term(x, y, text, text_color, bg_color)
  term.setTextColor(text_color)
  term.setBackgroundColor(bg_color)
  term.setCursorPos(x,y)
  write(text)
end

function draw_line_term(x, y, length, color)
    term.setBackgroundColor(color)
    term.setCursorPos(x,y)
    term.write(string.rep(" ", length))
end

function bars()
	draw_line_term(1, 1, 51, colors.lime)
	draw_line_term(1, 19, 51, colors.lime)
	draw_text_term(12, 1, 'CraftNanny Module Installer', colors.gray, colors.lime)
	draw_text_term(17, 19, 'craftnanny.org', colors.gray, colors.lime)
end

if not fs.exists('bootstrap') then
	print("Git API not found - Downloading")
	shell.run("pastebin get WdiT6sR5 bootstrap")
	shell.run("bootstrap")
end

-- saves current token variable to local text file
function save_config()
  sw = fs.open("config.txt", "w")   
    sw.writeLine(token)
	sw.writeLine(module_name)
	sw.writeLine(username)
	sw.writeLine(type)
  sw.close()
end

function load_config()
  sr = fs.open("config.txt", "r")
    token = sr.readLine()
	module_name = sr.readLine()
	username = sr.readLine()
	type = sr.readLine()
  sr.close()
end

function launch_module()
  shell.run("CN_module")
end

function install_module()
	if type == '1' then
		pastebin = energy_module
	elseif type == '2' then
		pastebin = fluid_module
	end
	
	term.clear()
	bars()
	draw_text_term(1, 3, 'successfully logged in', colors.lime, colors.black)
	sleep(0.5)
	draw_text_term(1, 4, 'installing...', colors.white, colors.black)
	sleep(0.5)
	
	draw_text_term(1, 5, 'removing old versions', colors.white, colors.black)
	if fs.exists("CN_module") then
	  fs.delete("CN_module")
	end
	sleep(0.5)
	
	draw_text_term(1, 6, 'fetch from github repo', colors.white, colors.black)
	term.setCursorPos(1,7)
	term.setTextColor(colors.white)
  shell.run("github get "..pastebin.." CN_module")
  sleep(0.5)
  
  draw_text_term(1, 9, 'create startup file', colors.white, colors.black)
	term.setCursorPos(1,10)
	term.setTextColor(colors.white)
  if fs.exists("startup") then
    fs.delete("startup")
  end
  shell.run("github get "..startup.." startup")
  sleep(1)
  
  draw_text_term(1, 13, 'Setup Complete', colors.lime, colors.black)

  draw_text_term(1, 14, 'press enter to continue', colors.lightGray, colors.black)
  
  if updating then
  
  else
  	input = read()
  end
  
  launch_module()
end

function hash(password)
	shell.run("github get "..hash_api.." sha1_api")
	os.loadAPI('sha1_api')
	response = http.post(
                "https://craftnanny.org/code/salt.php",
                "user="..user)
	salt = response.readAll()
	hash = sha1_api.sha1(salt..password)
	return hash
end

function login()
	term.clear()
	bars()
	draw_text_term(1, 3, 'Register module to your CraftNanny account.', colors.lime, colors.black)
	draw_text_term(1, 4, 'Create an account at www.craftnanny.org', colors.lightGray, colors.black)
	
	draw_text_term(1, 6, 'Username: ', colors.lime, colors.black)
	term.setTextColor(colors.white)
	user = read()
	draw_text_term(1, 7, 'Password: ', colors.lime, colors.black)
	term.setTextColor(colors.white)
	pass = read("*")
	
	password = hash(pass)
	
	response = http.post(
                "https://craftnanny.org/code/signin.php",
                "user="..user.."&pass="..password.."&id="..os.getComputerID().."&name="..module_name.."&module_type="..type)
	token = response.readAll()

	if token == 'error' then
		draw_text_term(1, 8, 'login failed', colors.red, colors.black)
		sleep(2)
		login()
	else 
		username = user
		save_config()
		install_module()
	end
end

function name()
	term.clear()
	bars()
	
	draw_text_term(1, 3, 'Give this module a unique name:', colors.lime, colors.black)
	term.setCursorPos(2,4)
	term.setTextColor(colors.white)
	module_name = read()
	os.setComputerLabel(string.gsub(module_name, "%s+", ""))
	login()
end

function player_tracker()
	
	-- code to check that openperipheral sensor is present. give relavent error
	
	type = '1'
	name()
end

function choose_module(input) 
	if input == '1' then
		type = '1'
		name()
	elseif input == '2' then
		type = '2'
		name()
	
	end
	
end

function install_select()
	term.clear()
	bars()
	draw_text_term(15, 3, 'Welcome to CraftNanny!', colors.lime, colors.black)
	draw_text_term(1, 5, 'What module would you like to install?', colors.white, colors.black)
	
	draw_text_term(2, 7, '1. Player Tracking', colors.white, colors.black)
	draw_text_term(2, 8, '2. Energy Monitor', colors.white, colors.black)
	draw_text_term(2, 9, 'Enter number:', colors.white, colors.black)
	term.setCursorPos(1,14)
	term.setTextColor(colors.white)
	input = read()
	
	choose_module(input)
end

function start()
  term.clear()
  if fs.exists("config.txt") then
  	load_config()
	updating = true
    install_module()
  else
    install_select()
  end
end

start()
