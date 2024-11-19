local containers={}
local version = 1

local config = { token = "0", module_name = "", username = "", type = "" }
local installer = ""


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
      draw_text_term(15, 1, 'CraftNanny Redstone Module', colors.gray, colors.lime)
      draw_text_term(10, 19, 'craftnanny.org', colors.gray, colors.lime)
  end

function terminal_screen()
	term.clear()
	
	bars()
	draw_text_term(1, 2, 'Module: ', colors.lime, colors.black)
	draw_text_term(10, 2, config.module_name, colors.white, colors.black)
	draw_text_term(1, 3, 'Owner: ', colors.lime, colors.black)
	draw_text_term(8, 3, config.username, colors.white, colors.black)
	draw_text_term(1, 4 , string.rep("-", 51), colors.lime, colors.black)
	
	--draw_text_term(2, 8, "I dont know what to put here...", colors.white, colors.black)
end

function downloadFromBackEnd(module_name, destination)
    local url = string.format("https://craftnanny.org/modules/dev/%s", module_name)
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
-- called at startup if config.txt exists
-- token is used to authorize the scanner to post to users log
function load_config()
    sr = fs.open("config.txt", "r")
    config.token = sr.readLine()
    config.module_name = sr.readLine()
	config.username = sr.readLine()
    sr.close()
end
-- called for new installations and when the scanner needs to be updated
function run_installer()
    if fs.exists(installer) then fs.delete(installer) end
    downloadFromBackend(installer, installer)
    sleep(1)
    shell.run(installer)
end


------  Start module specific code ---------

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

function phone_home(tank_name, fluid_type, percent)
    response = http.post("https://craftnanny.org/code/fluid.php",
    			"token="..config.token.."&id="..os.getComputerID().."&tank_name="..tank_name.."&fluid_type="..fluid_type.."&percent="..percent)		
	return_string = response.readAll()
end

function findSide()
    local faces = {"left", "right", "bottom", "top", "back"}
    for _, face in ipairs(faces) do
        if peripheral.isPresent(face) then
            local peripheralType = peripheral.getType(face)
            -- Check if the peripheral type contains "FluidTank"
            if peripheralType and string.find(peripheralType, "FluidTank") then
                return true, face
            end
        end
    end
    return false, ""
end

function round(num, decimals)
    local mult = 10^(decimals or 0)
    return math.floor(num * mult + 0.5) / mult
end

function capitalizeAndRemoveUnderscores(str)
    return str:gsub("([^_]+)([^_]*)", function(first, rest)
        first = first:gsub("^%l", string.upper) -- Capitalize the first letter of the first word
        rest = rest:gsub("^%l", string.upper)   -- Capitalize the first letter of the second word
        return first:gsub("_", "") .. rest:gsub("_", "") -- Concatenate the words after removing underscores
    end)
end



function getTankInformation(t, tankName)
    local tnk = peripheral.wrap(t)

    local okLiquid, msg = tnk.getStored()

    if okLiquid then 
        local amount = okLiquid.amount
        local capacity = tnk.getCapacity()
        local fluid = tostring(okLiquid.name):gsub("^[^:]*:", "")  -- Remove anything before the first colon
        fluid = capitalizeAndRemoveUnderscores(fluid) -- Capitalize the first letter 
        local percent = round((amount / capacity * 100), 2)

        
        phone_home(tankName, fluid, percent)
        
        print(fluid, ":")
        local graphBar = round(((term.getSize() * percent) / 100), 0)
        if graphBar < 50 then 
            draw_line_term(6, 7, graphBar , colors.green)
            draw_line_term(6 + graphBar, 7, term.getSize() - graphBar - 6, colors.red)
            draw_text_term(1, 7, percent .. " % ", colors.lime, colors.black)
            term.setBackgroundColor(colors.black)
        else
            draw_line_term(6, 7, graphBar - 6 , colors.green)
            draw_text_term(1, 7, percent .. " % ", colors.lime, colors.black)
            term.setBackgroundColor(colors.black)
        end
        return true
    else
        return false
    end
end


function start_loop()
    local ok, side = findSide()
    -- If no peripheral is found, exit the loop
    if not ok then
        print("No tank storage found")
        print("Please check your modems")
        return
    end

    while true do
        terminal_screen()
        ping_home()


        tanks = peripheral.getNames()
    -- getTankInformation(tank, tank)
        if #tanks > 2 then
            print("Only one device is supported")
            break
        elseif #tanks == 2 then
            for _, tank in pairs(tanks) do
                if tank ~= side then
                    ok = getTankInformation(tank, tank)          
                end
            end
        else
            ok = getTankInformation(side, "Tank"..os.getComputerID())
        end

        if not ok then 
            print("No tank storage found")
            print("Do you have the right module?")
            print("Please check your modems")
            break
        end

        -- Main active status with server
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