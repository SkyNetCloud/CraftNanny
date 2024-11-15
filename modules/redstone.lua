-- CraftNanny
-- Redstone module
local version = 1
 
-- pastebin for installer
local installer = "installer.lua"
local time = 0
local token = '0'
-- this scanners name
local module_name = ''
-- owenrs username on website
local username = ''
local type = ''
 
local faces = {top = "", bottom = "", front= "", back = "", left = "", right = ""}
 
local top_input = 0
local bottom_input = 0
local front_input = 0
local back_input = 0
local left_input = 0
local right_input = 0
 
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
    draw_text_term(10, 2, module_name, colors.white, colors.black)
    draw_text_term(1, 3, 'Owner: ', colors.lime, colors.black)
    draw_text_term(8, 3, username, colors.white, colors.black)
    draw_text_term(1, 4 , string.rep("-", 51), colors.lime, colors.black)
    
    draw_text_term(2, 6, 'Top: '.. faces.top, colors.white, colors.black)
    draw_text_term(2, 7, 'Bottom: '..faces.bottom, colors.white, colors.black)
    draw_text_term(2, 8, 'Front: '..faces.front, colors.white, colors.black)
    draw_text_term(2, 9, 'Back: '..faces.back, colors.white, colors.black)
    draw_text_term(2, 10, 'Right: '..faces.right, colors.white, colors.black)
    draw_text_term(2, 11, 'Left: '..faces.left, colors.white, colors.black)
end
 
function downloadFromBackEnd(module_name, destination)
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
-- called at startup if config.txt exists
-- token is used to authorize the scanner to post to users log
function load_config()
    sr = fs.open("config.txt", "r")
    token = sr.readLine()
    module_name = sr.readLine()
	username = sr.readLine()
    sr.close()
end

-- called for new installations and when the scanner needs to be updated
function run_installer()
    if fs.exists("installer.lua") then
        fs.delete("installer.lua")
    end
    downloadFromBackEnd(installer,installer)
    sleep(1)
    shell.run("installer.lua")
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
function string:split(delimiter)
  local result = { }
  local from  = 1
  local delim_from, delim_to = string.find( self, delimiter, from  )
  while delim_from do
    table.insert( result, string.sub( self, from , delim_from-1 ) )
    from  = delim_to + 1
    delim_from, delim_to = string.find( self, delimiter, from  )
  end
  table.insert( result, string.sub( self, from  ) )
  return result
end
 
function phone_home()
    getInputs()
 
    response = http.post("https://craftnanny.org/code/redstone.php",
         "token="..token.."&id="..os.getComputerID().."&top_input="..top_input.."&bottom_input="..bottom_input.."&front_input="..front_input.."&back_input="..back_input.."&left_input="..left_input.."&right_input="..right_input)      
    return_string = response.readAll()
    
    result_array = string.split(return_string,",")

    
    if tonumber(result_array[2]) == 1 then
        rs.setOutput('top', true)
        faces.top = 'true'
    else
        rs.setOutput('top', false)
        faces.top = 'false'
    end
    if tonumber(result_array[3]) == 1 then
        rs.setOutput('bottom', true)
        faces.bottom = 'true'
    else
        rs.setOutput('bottom', false)
        faces.bottom = 'false'
    end
    if tonumber(result_array[4]) == 1 then
        rs.setOutput('back', true)
        faces.back = 'true'
    else
        rs.setOutput('back', false)
        faces.back = 'false'
    end
    if tonumber(result_array[5]) == 1 then
        rs.setOutput('front', true)
        faces.front = 'true'
    else
        rs.setOutput('front', false)
        faces.front = 'false'
    end
    if tonumber(result_array[6]) == 1 then
        rs.setOutput('left', true)
        faces.left = 'true'
    else
        rs.setOutput('left', false)
        faces.left = 'false'
    end
    if tonumber(result_array[7]) == 1 then
        rs.setOutput('right', true)
        faces.right = 'true'
    else
        rs.setOutput('right', false)
        faces.right = 'false'
    end
end
 
function getInputs()
    if rs.getInput('top') then
        top_input = 1
    else
        top_input = 0
    end
    if rs.getInput('bottom') then
        bottom_input = 1
    else
        bottom_input = 0
    end
    if rs.getInput('front') then
        front_input = 1
    else
        front_input = 0
    end
    if rs.getInput('back') then
        back_input = 1
    else
        back_input = 0
    end
    if rs.getInput('left') then
        left_input = 1
    else
        left_input = 0
    end
    if rs.getInput('right') then
        right_input = 1
    else
        right_input = 0
    end
    
end
 
------  End module specific code ---------
 
 
function start_loop()
    phone_home()
    while true do
        terminal_screen()
        ping_home()

        -- main active status with server
        time = time + 1
        if time > 30 then
            time=0
            phone_home()
        end
        sleep(1)
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