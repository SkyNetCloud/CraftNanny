local bat={}
local version = 3

local installer = "installer.lua"
local token = '0'
local module_name = ''
local username = ''
local type = ''

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
	draw_text_term(15, 1, 'CraftNanny Energy Module', colors.gray, colors.lime)
	draw_text_term(10, 19, 'craftnanny.org', colors.gray, colors.lime)
end

function terminal_screen()
	term.clear()
	bars()
	draw_text_term(1, 2, 'Module: ', colors.lime, colors.black)
	draw_text_term(10, 2, module_name, colors.white, colors.black)
	draw_text_term(1, 3, 'Owner: ', colors.lime, colors.black)
	draw_text_term(8, 3, username, colors.white, colors.black)
	draw_text_term(1, 4 , string.rep("-", 51), colors.lime, colors.black)
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


------  Start module specific code ---------

function ping_home()
    response = http.post("https://craftnanny.org/code/ping.php",
        "token="..token.."&id="..os.getComputerID())
    current_version = response.readAll()
    
    if tonumber(current_version) > version then
      run_installer()
    end
end

function phone_home(bat_name, energy_type, percent)
    response = http.post("https://craftnanny.org/code/energy.php",
        "token="..token.."&id="..os.getComputerID().."&bat_name="..bat_name.."&energy_type="..energy_type.."&percent="..percent)
    --return_string = response.readAll()
end

function findSide()
    local faces = {"left", "right", "bottom", "top", "back"}
    for _, face in ipairs(faces) do
        if peripheral.isPresent(face) then
            local peripheralType = peripheral.getType(face)
            if peripheralType == "advancedEnergyCude" then
                return true, face
            end
        end
    end
    return false, ""
end


function round(num, idp)
    local mult = 10^(idp or 0)
    return math.floor(num * mult + 0.5) / mult
end

function getBat(t,batName)
    bt=peripheral.wrap(t)

    okFE,msg = pcall(bt.getMaxEnergy)
    okRF,msg = pcall(bt.getEnergyCapacity)
    okIM,msg = pcall(bt.getMaxEnergyStored)


    if okIM then 
        capacity=bt.getMaxEnergyStored()
        batAmount=bt.getEnergyStored()
        batContentName="FE"
    elseif okRF then 
        capacity=bt.getEnergyCapacity()
        batAmount=bt.getEnergy()
        batContentName="RF"
    elseif okFE then
        capacity=bt.getMaxEnergy()
        batAmount=bt.getEnergy()
        batContentName="FE"
    else
        return false
    end

    percent=round((batAmount/capacity*100),2)

    phone_home(batName, batContentName, percent)
    print(batName," ",batContentName," :")
    powerBar = round(((term.getSize()*percent)/100),0)
    if powerBar < 50 then
        draw_line_term(6, 7, powerBar , colors.green)
        draw_line_term(6+powerBar,7,term.getSize()-powerBar-6,colors.red)
        draw_text_term(1,7,percent.." % ",colors.lime,colors.black)
        term.setBackgroundColor(colors.black)
    else
        draw_line_term(6, 7, powerBar -6 , colors.green)
        draw_text_term(1,7,percent.." % ",colors.lime,colors.black)
        term.setBackgroundColor(colors.black)
    end
    return true
end

function nostorage()
	-- relevant error msg
end


function start_loop()
    ok,side=findSide ()
    if not ok then
        nostorage()
    end
    bats = peripheral.getNames()
    while true do
        terminal_screen()
		ping_home()
        if #bats >2 then
            print("Only one device is supported")
            break
        elseif  #bats == 2 then
            for batNo,bat in pairs(bats) do
                if bat~=side then
                    ok = getBat(bat,bat)
                end
            end
        else
            ok = getBat(side,"Battery"..os.getComputerID())
        end
        if not ok then
            print("No power storage found")
            print("Do you have the right module?")
            print("remove all file except install to reset")
            break
        end
        sleep(30)
    end
end

function start()
    term.clear()
    term.setCursorPos(1,1)

    if fs.exists("config.txt") then
        load_config()
        start_loop()
    else
        run_installer()
    end
end

start()