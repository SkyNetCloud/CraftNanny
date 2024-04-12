-- Global Variables
--allowedPlayerArray={["Topher"]=true,["nunley21"]=true,["Demethan"]=true,["waerloga"]=true}

-- pastebin for installer
local installer = "installer.lua"
-- players ignored by senors
local allowedPlayerArray={}
local playersInRange = {}
-- inventory arrays to compare
-- players currently inside sensor range 
local flag={}
-- counter for scanning animation
local heart = 0
-- counter for phone_home() 
local time = 0
-- user token to send to server
local token = '0'
-- this scanners name
local scanner = ''
-- oweners username on website
local username = ''
-- currently installed version
local version = 5

-- write text to the terminal screen
function draw_text_term(x, y, text, text_color, bg_color)
    term.setTextColor(text_color)
    term.setBackgroundColor(bg_color)
    term.setCursorPos(x,y)
    write(text)
end

-- draw a line on the terminal screen
function draw_line_term(x, y, length, color)
    term.setBackgroundColor(color)
    term.setCursorPos(x,y)
    term.write(string.rep(" ", length))
end

function bars()
	draw_line_term(1, 1, 51, colors.lime)
	draw_line_term(1, 19, 51, colors.lime)
	draw_text_term(20, 1, 'Base Scanner', colors.gray, colors.lime)
	draw_text_term(13, 19, 'CraftNanny.org', colors.gray, colors.lime)
end

function scanner_screen()
	term.clear()
	
	-- scanner info
	bars()
	draw_text_term(1, 2, 'Scanner: ', colors.lime, colors.black)
	draw_text_term(10, 2, scanner, colors.white, colors.black)
	draw_text_term(1, 3, 'Owner: ', colors.lime, colors.black)
	draw_text_term(8, 3, username, colors.white, colors.black)
	draw_text_term(1, 4 , string.rep("-", 51), colors.lime, colors.black)
	
	-- scanning graphic
	heart = heart + 1
	draw_text_term(3, 10, 'Scanning ', colors.red, colors.black)
	draw_text_term(12, 10 , string.rep(".", heart), colors.red, colors.black)
	if heart == 15 then
		heart = 1
	end
end

function downloadFromGitHub(file)

	local url = "https://raw.githubusercontent.com/SkyNetCloud/CraftNanny/master/modules/".. installer
	local localPath = fs.combine(shell.dir(), installer)
	local response = http.get(url)
	if response then
		local content = response.readAll()
		response.close()
		local file = fs.open(localPath, "w")
		file.write(content)
		file.close()
		return true
	else
		print("Failed to download file: " .. file)
		return false
	end
  end

-- retrieves token from local text file
-- called at startup if config.txt exists
-- token is used to authorize the scanner to post to users log
function load_config()
    sr = fs.open("config.txt", "r")
    token = sr.readLine()
	scanner = sr.readLine()
	username = sr.readLine()
    sr.close()
end

-- called for new installations and when the scanner needs to be updated
function run_installer()
    if fs.exists("installer.lua") then
        fs.delete("installer.lua")
    end
    downloadFromGitHub(installer)
    sleep(1)
    shell.run("installer.lua")
end

-- called every 30 seconds when scanner is running
-- tells the server that the scanner is online
-- checks version and automtically updates
function phone_home()
    response = http.post("https://craftnanny.org/code/ping.php",
        "token="..token.."&id="..os.getComputerID())
	current_version = response.readAll()

	if tonumber(current_version) > version then
        run_installer()
	end
end

function round(what, precision)
   if what==nil then return 0 end
   return math.floor(what*math.pow(10,precision)+0.5) / math.pow(10,precision)
end



function record()
    local onlinePlayers = s.getOnlinePlayers()
    
    for _, playerName in ipairs(onlinePlayers) do
        local inRange = s.isPlayerInRange(playerName, 15)
        
        if inRange then
            if not playersInRange[playerName] then
                post(playerName, 1, "has entered sensor range")
				pirnt(playerName)
                playersInRange[playerName] = true
            end
        else
            if playersInRange[playerName] then
                post(playerName, 2, "has left sensor range")
				pirnt(playerName)
                playersInRange[playerName] = nil
            end
        end
    end
end



-- -- iterate through all players with an active flag
-- -- see if they're still in range of the scanner
-- function leaveCheck()
--     for ign, inRange in pairs(flag) do
--         local isInRange = s.getPlayersInRange(15)
        
--         -- If the player is no longer in range and was previously flagged as in range
--         if not isInRange and inRange then
--             flag[ign] = false
--             post(tostring(ign), 2, " has left sensor range")
--         end
--     end
-- end



-- e.g. post('tom', 2, ' has left sensor range')
function post(ign, event, description)  
    http.post("https://craftnanny.org/code/log.php",
        "token="..token.."&ign="..ign.."&id="..os.getComputerID().."&event="..event.."&description="..description)
end

function tablelength(T) --get real count of table
    local count = 0
    for _ in pairs(T) do
        count = count + 1
    end
    return count
end

function compare(t1,t2)
	local ai = {}
    local r = {}
	table.sort(t1) --sorting by name
	table.sort(t2) --sorting by name
	
	for k,v in pairs(t2) do 
		r[k] = v; ai[v]=true 
	end
	for k,v in pairs(t1) do 
		if ai[v]~=nil then   --if item match, remove it from temp table r
			r[k]=nil   
		end
	end
	
	if tablelength(r) > 0 then --if there are items left in r 
		 return true,r
	else
		return false,nil
	end
end

function start_recording()
	-- main loop


	
	while true do
	
		ok, msg = pcall(record)

		-- run scan
		-- animate screen and delay
		scanner_screen()	
		sleep(0.5)
		scanner_screen()	
		sleep(0.5)
		
		-- main active status with server
		time = time + 5
		if time > 30 then
			time=0
			phone_home()
		end
	end
end

function start()
	s=peripheral.find("playerDetector")
	heart=0
	term.clear()
	term.setCursorPos(1,1)
	
    if fs.exists("config.txt") then
        load_config()
        start_recording()
    else
        run_installer()
    end
end

start()
