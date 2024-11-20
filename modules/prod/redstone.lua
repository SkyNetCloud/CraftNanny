-- CraftNanny
-- Redstone Module
local version = 1

-- Installer information
local installer = "dev_installer.lua"
local time = 0
local token = '0'
local module_name = ''
local username = ''
local faces = {top = "", bottom = "", front = "", back = "", left = "", right = ""}

-- Inputs for redstone signals
local inputs = {top = 0, bottom = 0, front = 0, back = 0, left = 0, right = 0}

-- Utility Functions

-- Write text to the terminal screen
local function drawText(x, y, text, textColor, bgColor)
    term.setTextColor(textColor)
    term.setBackgroundColor(bgColor)
    term.setCursorPos(x, y)
    write(text)
end

-- Draw a line on the terminal screen
local function drawLine(x, y, length, color)
    term.setBackgroundColor(color)
    term.setCursorPos(x, y)
    term.write(string.rep(" ", length))
end

-- Draw the terminal UI
local function drawBars()
    drawLine(1, 1, 51, colors.lime)
    drawLine(1, 19, 51, colors.lime)
    drawText(15, 1, 'CraftNanny Redstone Module', colors.gray, colors.lime)
    drawText(10, 19, 'craftnanny.org', colors.gray, colors.lime)
end

local function updateTerminal()
    term.clear()
    drawBars()
    drawText(1, 2, 'Module: ', colors.lime, colors.black)
    drawText(10, 2, module_name, colors.white, colors.black)
    drawText(1, 3, 'Owner: ', colors.lime, colors.black)
    drawText(8, 3, username, colors.white, colors.black)
    drawText(1, 4, string.rep("-", 51), colors.lime, colors.black)

    for i, face in ipairs({"top", "bottom", "front", "back", "left", "right"}) do
        drawText(2, 5 + i, face:sub(1, 1):upper() .. face:sub(2) .. ": " .. faces[face], colors.white, colors.black)
    end
end

-- Download a file from the backend
local function downloadFromBackend(module, destination)
    local url = "https://craftnanny.org/modules/dev/" .. module
    local site, err = http.get(url)
    if not site then error("Failed to contact site: " .. (err or "Unknown error")) end

    local content = site.readAll()
    site.close()

    if not content or content == "" then error("Failed to download content from " .. url) end

    local file = fs.open(destination, "w")
    file.write(content)
    file.close()
    print("Downloaded " .. module .. " successfully.")
end

-- Load the configuration file
local function loadConfig()
    local file = fs.open("config.txt", "r")
    token = file.readLine()
    module_name = file.readLine()
    username = file.readLine()
    file.close()
end

-- Run the installer
function run_installer()
    if fs.exists(installer) then fs.delete(installer) end
    downloadFromBackend(installer, installer)
    sleep(1)
    shell.run(installer)
end

-- Ping the server to check for updates
local function pingServer()
    local url = "https://craftnanny.org/api/ping.php?token=" .. token .. "&id=" .. os.getComputerID()
    
    local response = http.get(url)

    if response then
        local rawResponse = response.readAll()

        local versionResponse = tonumber(rawResponse)

        if versionResponse then
            if versionResponse > version then
                runInstaller()
            end
        end

        response.close()
    end
end

-- Get redstone inputs from all faces
local function getInputs()
    for face, _ in pairs(inputs) do
        inputs[face] = rs.getInput(face) and 1 or 0
    end
end

-- Communicate with the server and update outputs
local function phoneHome()
    local url = "https://craftnanny.org/api/redstone.php"
    local postData = "token=" .. token .. "&id=" .. os.getComputerID()

    for face, value in pairs(inputs) do
        postData = postData .. "&" .. face .. "_input=" .. value
    end

    local response = http.post(url, postData)

    if response then
        local returnString = response.readAll()
        response.close()

        local resultArray = {}
        for result in string.gmatch(returnString, "[^,]+") do
            table.insert(resultArray, tonumber(result))
        end

        for i, face in ipairs({"top", "bottom", "back", "front", "left", "right"}) do
            local output = resultArray[i] == 1
            rs.setOutput(face, output)
            faces[face] = tostring(output)
        end
    end
end

-- Main loop
local function startLoop()
    phoneHome()
    while true do
        updateTerminal()
        pingServer()
        time = (time + 1) % 31
        if time == 0 then phoneHome() end
        sleep(1)
    end
end

-- Start the module
local function start()
    term.clear()
    term.setCursorPos(1, 1)

    if fs.exists("config.txt") then
        loadConfig()
        startLoop()
    else
        runInstaller()
    end
end

start()
