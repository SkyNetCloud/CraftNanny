term.clear()
local token = ''
local module_name = ''
local username = ''
local type = ''
local updating = false
local user = ''


function logger(message)
    local file = fs.open("login.log", "a")
    file.writeLine(os.date("%Y-%m-%d %H:%M:%S") .. " - " .. message)
    file.close()
end

local function downloadFromBackEnd(module_name, destination)
    local url = string.format("https://craftnanny.org/modules/prod/%s", module_name)
    
    -- Make the request to GitHub
    local site, err = http.get(url)
    
    -- Check if the request was successful
    if not site then
        -- If the request failed, print the error and exit
        error("Failed to contact Site: " .. (err or "Unknown error"))
    end

    -- Read the content from the response
    local content = site.readAll()
    site.close()

    -- If content is nil or empty, something went wrong
    if not content or content == "" then
        error("Failed to download content from " .. url)
    end

    -- Save the content to the specified file
    local file = fs.open(destination, "w")
    file.write(content)
    file.close()

    print("Downloaded " .. module_name .. " from Site successfully.")
end

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
    shell.run("startup.lua")
end

function install_module()
    if type == '1' then
        pastebin = "energy.lua"
    elseif type == '2' then
        pastebin = "fluid.lua"
    elseif type == '3' then
        pastebin = 'redstone.lua'
    elseif type == '4' then
        pastebin = 'reactor.lua'
    end

    term.clear()
	bars()
	draw_text_term(1, 3, 'Successfully logged in', colors.lime, colors.black)
	sleep(0.5)
	draw_text_term(1, 4, 'Installing...', colors.white, colors.black)
	sleep(0.5)
	
	draw_text_term(1, 5, 'Removing old versions', colors.white, colors.black)
	if fs.exists(pastebin) then
	    fs.delete(pastebin)
	end
	sleep(0.5)
	
	draw_text_term(1, 6, 'Fetching from CraftNanny website', colors.white, colors.black)
	term.setCursorPos(1,7)
	term.setTextColor(colors.white)
    downloadFromBackEnd(pastebin, pastebin)
    
    sleep(0.5)
  
    draw_text_term(1, 9, 'Create startup file', colors.white, colors.black)
	term.setCursorPos(1,10)
	term.setTextColor(colors.white)
    if fs.exists("startup") then
        fs.delete("startup")
    end
    downloadFromBackEnd("startup.lua", "startup.lua")
    sleep(1)
  
    draw_text_term(1, 13, 'Setup Complete', colors.lime, colors.black)

    draw_text_term(1, 14, 'Press Enter to Continue', colors.lightGray, colors.black)

    if updating then

    else
        input = read()
    end

    launch_module()

end



function urlencode(str)
    return (str:gsub("([^%w])", function(c)
        return string.format("%%%02X", string.byte(c))
    end))
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
    
    local queryData = string.format("user=%s&pass=%s&id=%s&name=%s&module_type=%s",
    user, urlencode(pass), os.getComputerID(), module_name, type)

    local login_response, err = http.get("https://craftnanny.org/api/signin.php?" .. queryData)
    

    if not login_response then
        logger("Login HTTP request failed: " .. (err or "Unknown error"))
        draw_text_term(1, 8, 'Login failed: HTTP error', colors.red, colors.black)
        sleep(2)
        login()  -- Retry login
        return
    end

    token = login_response.readAll()

    if not token or token == 'error: User not found' then
        logger("Login failed for user '" .. user .. "'. Response: " .. (token or "nil"))
        draw_text_term(1, 8, 'Login failed: User not found', colors.red, colors.black)
        sleep(2)
        login()  -- Retry login
    else
        username = user
        save_config()  -- Save configuration
        install_module()  -- Proceed with module installation
    end
end


function name()
    term.clear()
    bars()
    
    draw_text_term(1, 3, 'Give this module a unique name:', colors.lime, colors.black)
    term.setCursorPos(2,4)
    term.setTextColor(colors.white)
    module_name = read()
    login()
end

function choose_module(input) 
    if input == '1' then
        type = '1'
        name()
    elseif input == '2' then
        type = '2'
        name()
    elseif input == '3' then
        type = '3'
        name()
    elseif input == '4' then
        type = '4'
        name()
    end
end

function install_select()
    term.clear()
    bars()
    draw_text_term(15, 3, 'Welcome to CraftNanny!', colors.lime, colors.black)
    draw_text_term(1, 5, 'What module would you like to install?', colors.white, colors.black)
    
    draw_text_term(2, 8, '1. Energy Monitor', colors.white, colors.black)
    draw_text_term(2, 9, '2. Fluid Monitor', colors.white, colors.black)
    draw_text_term(2, 10,'3. Redstone Controller', colors.white, colors.black)
    draw_text_term(2, 11,'4. Reactor Controller', colors.white, colors.black)
    draw_text_term(1, 13, 'Enter number:', colors.white, colors.black)
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
