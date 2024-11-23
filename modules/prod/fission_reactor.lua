local reactor = {}
local version = 1

local config = { token = "0", module_name = "", username = "", type = "" }
local installer = "dev_installer.lua"
local faces = { "left", "right", "bottom", "top", "back" }

-- Define critical thresholds for failsafe
local CRITICAL_TEMPERATURE = 1200 -- Adjust this value as necessary
local CRITICAL_WASTE_PERCENTAGE = 90
local CRITICAL_COOLANT_PERCENTAGE = 1

local function loadConfig()
    local file = fs.open("config.txt", "r")
    config.token = file.readLine()
    config.module_name = file.readLine()
    config.username = file.readLine()
    file.close()
end

function run_installer()
    if fs.exists(installer) then fs.delete(installer) end
    download_from_backend(installer, installer)
    shell.run(installer)
end

-- Write text to the terminal screen
function draw_text_term(x, y, text, text_color, bg_color)
    term.setTextColor(text_color)
    term.setBackgroundColor(bg_color)
    term.setCursorPos(x, y)
    write(text)
end

-- Draw a line on the terminal screen
function draw_line_term(x, y, length, color)
    term.setBackgroundColor(color)
    term.setCursorPos(x, y)
    term.write(string.rep(" ", length))
end

function bars()
    draw_line_term(1, 1, 51, colors.lime)
    draw_line_term(1, 19, 51, colors.lime)
    draw_text_term(15, 1, 'CraftNanny Reactor Module', colors.gray, colors.lime)
    draw_text_term(10, 19, 'craftnanny.org', colors.gray, colors.lime)
end

function terminal_screen(data)
    term.clear()
    bars()
    draw_text_term(1, 2, 'Module: ', colors.lime, colors.black)
    draw_text_term(10, 2, config.module_name, colors.white, colors.black)
    draw_text_term(1, 3, 'Owner: ', colors.lime, colors.black)
    draw_text_term(8, 3, config.username, colors.white, colors.black)
    draw_text_term(1, 4, string.rep("-", 51), colors.lime, colors.black)

    -- Display reactor-specific data
    local y = 5
    for key, value in pairs(data) do
        if key == "StatusDisplay" then
            draw_line_term(1, y, 51, colors.black)
            draw_text_term(1, y, string.format("%-20s", "Status:"), colors.lime, colors.black)
            draw_text_term(17, y, value, colors.white, colors.black)
        elseif key == "Waste Percentage" or key == "Coolant Percentage" or key == "Fuel Percentage" then
            draw_line_term(1, y, 51, colors.black)
            draw_text_term(1, y, string.format("%-20s", key), colors.lime, colors.black)
            draw_text_term(17, y, string.format("%5.2f%%", tonumber(value) or 0), colors.white, colors.black)
        else
            draw_line_term(1, y, 51, colors.black)
            draw_text_term(1, y, key .. ":          ", colors.lime, colors.black)
            draw_text_term(17, y, tostring(value), colors.white, colors.black)
        end
        y = y + 1
    end
end

function logger(message)
    -- Get information about the current function call
    local info = debug.getinfo(2, "l")  -- Get the line number of the calling function
    
    -- Get the current timestamp
    local timestamp = os.date("%Y-%m-%d %H:%M:%S")
    
    -- Format the log message with timestamp and line number
    local formattedMessage = string.format("[%s] [Line %d] %s", timestamp, info.currentline, message)
    
    -- Open the log file in append mode and write the log entry
    local sw = fs.open("logger.txt", "a")
    sw.writeLine(formattedMessage)
    sw.close()
    
    -- Optionally, print the log to the terminal as well for real-time feedback
    print(formattedMessage)
end

function download_from_backend(module_name, destination)
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

function newBurnRate(reactor)
    local file = fs.open("config.txt", "r")
    token = file.readLine()
    file.close()  -- Always close the file when you're done reading

    -- Send a GET request to fetch the burn rate and other necessary data
    local response, err = http.get("https://craftnanny.org/api/main.php?a=get_burn_rate&user_id=".. token)
    if response then
        local responseBody = response.readAll()
        logger("Server response: " .. responseBody)

        -- Assuming the server returns a JSON response with a burn_rate field
        local responseTable = textutils.unserializeJSON(responseBody)
        
        -- Check if the response contains the correct burn_rate
        if responseTable and responseTable.get_burn_rate then
            local burnRate = tonumber(responseTable.get_burn_rate[1].burn_rate)
            if burnRate then
                -- Apply the new burn rate to the reactor
                reactor.setBurnRate(burnRate)
                logger("New burn rate set: " .. burnRate)
            else
                logger("Invalid burn rate received.")
            end
        else
            logger("No burn rate in response or invalid JSON format.")
        end
    else
        logger("Error sending GET request: " .. (err or "Unknown error"))
    end
end


function phone_home(status,coolant,coolant_percentage,coolant_capacity,coolant_needed,coolant_heated,coolant_heated_capacity,coolant_heated_needed,fuel,fuel_capacity,fuel_needed,fuel_percentage,waste,waste_capacity,waste_needed,waste_percentage,status,max_burn_rate,reactor_damage_precentage,heat_rate,environmental_loss,temperature,heat_capacity,fuel_assmblies,fuel_surface_area, reactor)
    local url = "https://craftnanny.org/api/fission_reactor.php"
    local body = string.format(
        "token=%s&id=%s&status=%s&coolant=%s&coolant_percentage=%s&coolant_capacity=%s&coolant_needed=%s&coolant_heated=%s&coolant_heated_percentage=%s&coolant_heated_capacity=%s&coolant_heated_needed=%s&fuel=%s&fuel_capacity=%s&fuel_needed=%s&fuel_percentage=%s&waste=%s&waste_capacity=%s&waste_needed=%s&waste_percentage=%s&status=%s&max_burn_rate=%s&reactor_damage_precentage=%s&heat_rate=%s&environmental_loss=%s&temperature=%s&heat_capacity=%s&fuel_assmblies=%s&fuel_surface_area=%s",
        config.token,
        os.getComputerID(),
        coolant,
        coolant_percentage,
        coolant_capacity,
        coolant_needed,
        coolant_heated,
        coolant_heated_percentage,
        coolant_heated_capacity,
        coolant_heated_needed,
        fuel,
        fuel_capacity,
        fuel_needed,
        fuel_percentage,
        waste,
        waste_capacity,
        waste_needed,
        waste_percentage,
        status,
        max_burn_rate,
        reactor_damage_precentage,
        heat_rate,
        environmental_loss,
        temperature,
        heat_capacity,
        fuel_assmblies,
        fuel_surface_area
    )

    local response, err = http.post(url, body)
    if response then
        local responseBody = response.readAll()
        --logger("Server response: " .. responseBody)
    else
        --logger("Error sending data: " .. (err or "Unknown error"))
    end
end

function find_side()
    for _, face in ipairs(faces) do
        if peripheral.isPresent(face) and string.find(peripheral.getType(face) or "", "fissionReactorLogicAdapter") then
            return face
        end
    end
    return nil
end

-- function getReactorData(side)
--     local reactor = peripheral.wrap(side)
--     if not reactor then
--         print("No reactor found on side " .. side)
--         return nil, nil
--     end

--     local success, result
--     local data = {}

--     success, result = pcall(reactor.getStatus)
--     data.StatusDisplay = result and "Active" or "Deactive"

--     success, result = pcall(reactor.getBurnRate)
--     data["Burn Rate"] = success and result or 0

--     success, result = pcall(reactor.getCoolant)
--     data["Coolant"] = success and result.amount or 0

--     success, result = pcall(reactor.getFuelFilledPercentage)
--     data["Fuel Percentage"] = success and result or 0

--     success, result = pcall(reactor.getMaxBurnRate)
--     data["Max Burn Rate"] = success and result or 0

--     success, result = pcall(reactor.getTemperature)
--     data["Temperature"] = success and result or 0

--     success, result = pcall(reactor.getWaste)
--     data["Waste"] = success and result and result.amount or 0

--     success, result = pcall(reactor.getCoolantFilledPercentage)
--     data["Coolant Percentage"] = success and result or 0

--     success, result = pcall(reactor.getWasteFilledPercentage)
--     data["Waste Percentage"] = success and result or 0

--     success, result = pcall(reactor.getFuelCapacity)
--     data["Fuel Capacity"] = success and result or 0

--     return data, reactor
-- end

function safeCall(method, default, transform)
    local success, result = pcall(method)
    if success then
        return transform and transform(result) or result
    end
    return default
end

function checkFailsafe(data, reactor)
    if tonumber(data["Temperature"]) and data["Temperature"] > CRITICAL_TEMPERATURE then
        print("Critical temperature detected! Shutting down reactor...")
        reactor.scram()
        return true
    end

    if tonumber(data["Coolant Percentage"]) and data["Coolant Percentage"] < CRITICAL_COOLANT_PERCENTAGE then
        print("Critical coolant level detected! Shutting down reactor...")
        reactor.scram()
        return true
    end

    if tonumber(data["Waste Percentage"]) and data["Waste Percentage"] > CRITICAL_WASTE_PERCENTAGE then
        print("Critical waste level detected! Shutting down reactor...")
        reactor.scram()
        return true
    end

    return false
end

function start_loop()
    local side = find_side()
    if not side then
        print("No reactor found. Please check connections.")
        return
    end

    while true do
        local data, reactor = getReactorData(side)
        if not data or not reactor then
            print("Error: Unable to fetch reactor data.")
            break
        end

        local shutDown = checkFailsafe(data, reactor)
        if shutDown then
            print("Reactor has been shut down due to critical conditions.")
            break
        end

        -- Send data to the backend
        phone_home(
            data["StatusDisplay"] or "",
            data["Coolant"] or 0,
            data["Coolant Percentage"] or 0,
            data["Coolant Capacity"] or 0,
            data["Coolant Needed"] or 0,
            data["Coolant Heated"] or 0,
            data["Coolant Heated Percentage"] or 0,
            data["Coolant Heated Capacity"] or 0,
            data["Coolant Heated Needed"] or 0,
            data["Fuel"] or 0,
            data["Fuel Capacity"] or 0,
            data["Fuel Needed"] or 0,
            data["Fuel Percentage"] or 0,
            data["Waste"] or 0,
            data["Waste Capacity"] or 0,
            data["Waste Needed"] or 0,
            data["Waste Percentage"] or 0,
            data["Max Burn Rate"] or 0,
            data["Reactor Damage Percentage"] or 0,
            data["Heat Rate"] or 0,
            data["Environmental Loss"] or 0,
            data["Temperature"] or 0,
            data["Heat Capacity"] or 0,
            data["Fuel Assemblies"] or 0,
            data["Fuel Surface Area"] or 0
        )
        newBurnRate(reactor)

        terminal_screen(data)
        sleep(30) -- Adjust this delay as needed
    end
end

function start()
    term.clear()
    term.setCursorPos(1, 1)

    if fs.exists("config.txt") then
        loadConfig()
        start_loop()
    else
        run_installer()
    end
end

start()
