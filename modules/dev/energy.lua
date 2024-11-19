-- CraftNanny Energy Module
local version = 1
local config = { token = "0", module_name = "", username = "", type = "" }
local installer = "dev_installer.lua"
local faces = { "left", "right", "bottom", "top", "back" }

-- Utility Functions
local function draw_text_term(x, y, text, text_color, bg_color)
    term.setTextColor(text_color)
    term.setBackgroundColor(bg_color)
    term.setCursorPos(x, y)
    write(text)
end

local function draw_line_term(x, y, length, color)
    term.setBackgroundColor(color)
    term.setCursorPos(x, y)
    term.write(string.rep(" ", length))
end

local function round(num, idp)
    local mult = 10 ^ (idp or 0)
    return math.floor(num * mult + 0.5) / mult
end

-- UI Elements
local function bars()
    draw_line_term(1, 1, 51, colors.lime)
    draw_line_term(1, 19, 51, colors.lime)
    draw_text_term(15, 1, "CraftNanny Energy Module", colors.gray, colors.lime)
    draw_text_term(10, 19, "craftnanny.org", colors.gray, colors.lime)
end

local function terminal_screen()
    term.clear()
    bars()
    draw_text_term(1, 2, "Module: ", colors.lime, colors.black)
    draw_text_term(10, 2, config.module_name, colors.white, colors.black)
    draw_text_term(1, 3, "Owner: ", colors.lime, colors.black)
    draw_text_term(8, 3, config.username, colors.white, colors.black)
    draw_text_term(1, 4, string.rep("-", 51), colors.lime, colors.black)
end

-- Networking
local function download_from_backend(module_name, destination)
    local url = string.format("https://craftnanny.org/modules/dev/%s", module_name)
    local site, err = http.get(url)
    if not site then error("Failed to contact Site: " .. (err or "Unknown error")) end

    local content = site.readAll()
    site.close()
    if not content or content == "" then error("Failed to download content from " .. url) end

    local file = fs.open(destination, "w")
    file.write(content)
    file.close()
    print("Downloaded " .. module_name .. " successfully.")
end

local function phone_home(bat_name, energy_type, percent)
    http.post("https://craftnanny.org/code/energy.php",
        "token=" .. config.token ..
        "&id=" .. os.getComputerID() ..
        "&bat_name=" .. bat_name ..
        "&energy_type=" .. energy_type ..
        "&percent=" .. percent)
end

local function ping_home()
    local url = "https://craftnanny.org/code/ping.php?token=" .. config.token .. "&id=" .. os.getComputerID()
    if http.checkURL(url) then
        local response = http.get(url)
        if response then
            local response_body = response.readAll()
            if tonumber(response_body) > version then run_installer() end
            response.close()
        else
            print("Error: Failed to connect to server.")
        end
    else
        print("Error: URL check failed.")
    end
end

-- Config and Installer
local function load_config()
    if not fs.exists("config.txt") then error("Config file not found.") end
    local sr = fs.open("config.txt", "r")
    config.token = sr.readLine()
    config.module_name = sr.readLine()
    config.username = sr.readLine()
    sr.close()
end

local function run_installer()
    if fs.exists(installer) then fs.delete(installer) end
    download_from_backend(installer, installer)
    shell.run(installer)
end

-- Energy Monitoring
local function find_side()
    for _, face in ipairs(faces) do
        if peripheral.isPresent(face) and string.find(peripheral.getType(face) or "", "EnergyCube") then
            return face
        end
    end
    return nil
end

local function get_battery_status(side, bat_name)
    local peripheral_device = peripheral.wrap(side)
    if not peripheral_device then return false end

    local capacity, bat_amount, energy_type
    if peripheral_device.getMaxEnergy then
        capacity = peripheral_device.getMaxEnergy()
        bat_amount = peripheral_device.getEnergy()
        energy_type = "FE"
    elseif peripheral_device.getEnergyCapacity then
        capacity = peripheral_device.getEnergyCapacity()
        bat_amount = peripheral_device.getEnergy()
        energy_type = "RF"
    elseif peripheral_device.getMaxEnergyStored then
        capacity = peripheral_device.getMaxEnergyStored()
        bat_amount = peripheral_device.getEnergyStored()
        energy_type = "EU"
    else
        return false
    end

    local percent = round((bat_amount / capacity) * 100, 2)
    phone_home(bat_name, energy_type, percent)

    -- UI Power Bar
    local power_bar = math.floor((percent / 100) * 40)
    draw_line_term(6, 7, power_bar, colors.green)
    draw_line_term(6 + power_bar, 7, 40 - power_bar, colors.red)
    draw_text_term(1, 7, percent .. " %", colors.lime, colors.black)

    return true
end

-- Main Loop
local function start_loop()
    local side = find_side()
    if not side then
        print("No power storage found. Check connections or module setup.")
        return
    end

    while true do
        terminal_screen()
        ping_home()
        if not get_battery_status(side, "Battery " .. os.getComputerID()) then
            print("Failed to retrieve battery status.")
        end
        sleep(30)
    end
end

-- Startup
local function start()
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
