local bat={}
local version = 1

local config = { token = "0", module_name = "", username = "", type = "" }
local installer = "installer.lua"

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

local function downloadFromBackEnd(module_name, destination)
    local url = string.format("https://craftnanny.org/modules/%s", module_name)
    local site, err = http.get(url)
    if not site then
        error("Failed to contact Site: " .. (err or "Unknown error"))
    end
    local content = site.readAll()
    site.close()
    if not content or content == "" then
        error("Failed to download content from " .. url)
    end
    local file = fs.open(destination, "w")
    file.write(content)
    file.close()

    print("Downloaded " .. module_name .. " from Site successfully.")
end

-- retrieves token from local text file
function load_config()
    local sr = fs.open("config.txt", "r")
    token = sr.readLine()
    module_name = sr.readLine()
	username = sr.readLine()
    sr.close()
end

-- runs installer script for new installations or updates
function run_installer()
    if fs.exists("installer.lua") then
        fs.delete("installer.lua")
    end
    downloadFromBackEnd(installer,installer)
    sleep(1)
    shell.run("installer.lua")
end

function ping_home()
    local sr = fs.open("config.txt", "r")
    token = sr.readLine()

    local url = "https://craftnanny.org/code/ping.php?token=" .. token .. "&id=" .. os.getComputerID()

    if http.checkURL(url) then
        local response = http.get(url)
        if response then
            local responseBody = response.readAll()
            if tonumber(responseBody) > version then
            run_installer()
            end
            response.close()
        else
            print("Error: Failed to connect to server.")
        end
    else
        print("Error: URL check failed.")
    end
end
 

function phone_home(bat_name, energy_type, percent)
    http.post("https://craftnanny.org/code/energy.php",
        "token="..token.."&id="..os.getComputerID().."&bat_name="..bat_name.."&energy_type="..energy_type.."&percent="..percent)
end

function findSide()
    local faces = {"left", "right", "bottom", "top", "back"}
    for _, face in ipairs(faces) do
        if peripheral.isPresent(face) then
            local peripheralType = peripheral.getType(face)
            if peripheralType == "advancedEnergyCube" then
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

function getBat(side, batName)
    local bt = peripheral.wrap(side)

    local okFE, msg = pcall(bt.getMaxEnergy)
    local okRF, msg = pcall(bt.getEnergyCapacity)
    local okIM, msg = pcall(bt.getMaxEnergyStored)

    local capacity, batAmount, batContentName

    if okIM then 
        capacity = bt.getMaxEnergyStored()
        batAmount = bt.getEnergyStored()
        batContentName = "FE"
    elseif okRF then 
        capacity = bt.getEnergyCapacity()
        batAmount = bt.getEnergy()
        batContentName = "RF"
    elseif okFE then
        capacity = bt.getMaxEnergy()
        batAmount = bt.getEnergy()
        batContentName = "FE"
    else
        return false
    end

    local percent = round((batAmount / capacity * 100), 2)
    phone_home(batName, batContentName, percent)

    print(batName, " ", batContentName, " :")
    local powerBar = round(((term.getSize() * percent) / 100), 0)

    if powerBar < 50 then
        draw_line_term(6, 7, powerBar, colors.green)
        draw_line_term(6 + powerBar, 7, term.getSize() - powerBar - 6, colors.red)
        draw_text_term(1, 7, percent .. " % ", colors.lime, colors.black)
    else
        draw_line_term(6, 7, powerBar - 6, colors.green)
        draw_text_term(1, 7, percent .. " % ", colors.lime, colors.black)
    end
    term.setBackgroundColor(colors.black)
    return true
end

function nostorage()
    print("No power storage found")
    print("Ensure the correct module is connected")
end

function start_loop()
    local ok, side = findSide()
    if not ok then
        nostorage()
        return
    end

    while true do
        terminal_screen()
        ping_home()
        
        local connected = getBat(side, "Battery" .. os.getComputerID())
        if not connected then
            print("No power storage found")
            print("Check connections or module setup")
            break
        end

        sleep(30)
    end
end

function start()
    term.clear()
    term.setCursorPos(1, 1)

    if fs.exists("config.txt") then
        load_config()
        start_loop()
    else
        run_installer()
    end
end

start()
